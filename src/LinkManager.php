<?php

declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Created by: Sander Visser <themastersleader@hotmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 *
 * @link https://github.com/SanderSander/composer-link
 */

namespace ComposerLink;

use Composer\Composer;
use Composer\DependencyResolver\Request;
use Composer\Filter\PlatformRequirementFilter\IgnoreAllPlatformRequirementFilter;
use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\Link;
use Composer\Package\Version\VersionParser;
use Composer\Repository\ArrayRepository;
use Composer\Semver\Constraint\ConstraintInterface;
use Composer\Semver\Constraint\MatchAllConstraint;
use ComposerLink\Package\LinkedPackage;
use ComposerLink\Repository\Repository;
use ReflectionClass;

class LinkManager
{
    protected readonly ArrayRepository $linkedRepository;

    /**
     * @var array<string, Link>
     */
    protected array $requires = [];

    public function __construct(
        protected readonly Repository $repository,
        protected readonly InstallerFactory $installerFactory,
        protected readonly IOInterface $io,
        protected readonly Composer $composer,
    ) {
        $this->linkedRepository = new ArrayRepository();

        // Load linked packages
        foreach ($this->repository->all() as $package) {
            $this->registerPackage($package);
        }
    }

    public function add(LinkedPackage $package): void
    {
        $this->repository->store($package);
        $this->repository->persist();

        $this->registerPackage($package);
    }

    private function registerPackage(LinkedPackage $package): void
    {
        $rootPackage = $this->composer->getPackage();
        $this->linkedRepository->addPackage($package);

        $this->createAliasesForRequiresInLinkedPackage($package);
        $this->createAliasesForRequiresInLinkedPackages($package);

        $this->requires[$package->getName()] = $package->createLink($rootPackage);
    }

    /**
     * The linked package could contain requirements that point to already linked packages.
     * In those cases we need to create an alias to those linked packages.
     *
     * E.g.,
     * Link => package-1:dev-linked
     * Link => package-2:dev-linked (requires package-1:dev-main)
     * We create an alias from package-1:dev-main to package-1:dev-linked
     */
    private function createAliasesForRequiresInLinkedPackage(LinkedPackage $package): void
    {
        foreach ($package->getRequires() as $link) {
            $linked = $this->linkedRepository->findPackage($link->getTarget(), new MatchAllConstraint());
            $aliased = $this->linkedRepository->findPackage($link->getTarget(), $link->getConstraint());
            if (!is_null($linked) && is_null($aliased)) {
                $version = $this->getVersionFromConstraint($link->getConstraint());
                $this->linkedRepository->addPackage(new AliasPackage($linked, $version, $link->getPrettyConstraint()));
            }
        }
    }

    /**
     * Already linked packages could have required packages, while those required packages should also be linked.
     * In those cases we add an alias to handle the transitive requirements.
     *
     * E.g.,
     * Link => package-2:dev-linked -> requires: package-1:dev-main
     * Link => package-1:dev-linked (We create an alias from package-1:dev-main to dev-linked)
     */
    private function createAliasesForRequiresInLinkedPackages(LinkedPackage $toPackage): void
    {
        foreach ($this->linkedRepository->getPackages() as $linked) {
            foreach ($linked->getRequires() as $link) {
                $alias = $this->linkedRepository->findPackage($link->getTarget(), $link->getConstraint());
                if (!is_null($alias)) {
                    continue;
                }

                if ($link->getTarget() === $toPackage->getName()) {
                    $version = $this->getVersionFromConstraint($link->getConstraint());
                    $this->linkedRepository->addPackage(new AliasPackage($toPackage, $version, $link->getPrettyConstraint()));
                }
            }
        }
    }

    /**
     * Transforms version constraints to usable version strings.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function getVersionFromConstraint(ConstraintInterface $constraint): string
    {
        $stability = VersionParser::parseStability($constraint->getPrettyString());

        return $stability === 'dev' ?
            (new VersionParser())->normalize($constraint->getPrettyString()) :
            $constraint->getLowerBound()->getVersion();
    }

    public function remove(LinkedPackage $package): void
    {
        $this->linkedRepository->removePackage($package);
        $internalPackages = $this->linkedRepository->findPackages($package->getName());
        foreach ($internalPackages as $internalPackage) {
            $this->linkedRepository->removePackage($internalPackage);
        }

        unset($this->requires[$package->getName()]);

        $this->repository->remove($package);
        $this->repository->persist();
    }

    public function hasLinkedPackages(): bool
    {
        return $this->linkedRepository->count() > 0;
    }

    public function linkPackages(bool $isDev): void
    {
        $repositoryManager = $this->composer->getRepositoryManager();
        $eventDispatcher = $this->composer->getEventDispatcher();
        $rootPackage = $this->composer->getPackage();

        // Use the composer installer to install the linked packages with dependencies
        $repositoryManager->prependRepository($this->linkedRepository);

        // Add requirement to the current/loaded composer.json
        $rootPackage->setRequires(array_merge($rootPackage->getRequires(), $this->requires));
        $this->io->warning('<warning>Linking packages, Lock file will be generated in memory but not written to disk.</warning>');

        // Show extra added packages as information, this makes it a bit easier to debug
        foreach ($this->linkedRepository->getPackages() as $package) {
            $this->io->info((new ReflectionClass($package))->getShortName() . "\t\t" . $package->getName() . ':' . $package->getVersion() . ' - ' . $package->getPrettyVersion());
            if ($package instanceof AliasPackage) {
                $this->io->info("\t\t\t" . $package->getAliasOf()->getName() . ':' . $package->getAliasOf()->getVersion() . ' - ' . $package->getAliasOf()->getPrettyVersion());
            }
        }

        // We need to remove linked packages from the dev-require because we placed them in the section require
        $devRequires = $rootPackage->getDevRequires();
        foreach ($this->linkedRepository->getPackages() as $package) {
            unset($devRequires[$package->getName()]);
        }
        $rootPackage->setDevRequires($devRequires);

        // Prevent circular call to script handler 'post-update-cmd' by creating a new composer instance
        // We also need to set this on the Installer while it's deprecated
        $eventDispatcher->setRunScripts(false);
        $installer = $this->installerFactory->create();

        /* @phpstan-ignore method.deprecated */
        $installer->setUpdate(! empty($this->requires))
            ->setInstall(true)
            ->setWriteLock(false)
            ->setRunScripts(false)
            ->setUpdateAllowList(array_keys($this->requires))
            ->setPlatformRequirementFilter(new IgnoreAllPlatformRequirementFilter())
            ->setDevMode($isDev)
            ->setUpdateAllowTransitiveDependencies(Request::UPDATE_ONLY_LISTED);

        $installer->run();

        $eventDispatcher->setRunScripts();
        $this->io->warning('<warning>Linking packages finished!</warning>');
    }
}
