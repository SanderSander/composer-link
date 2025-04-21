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
use Composer\Repository\ArrayRepository;
use Composer\Semver\Constraint\MatchAllConstraint;
use ComposerLink\Package\LinkedPackage;
use ComposerLink\Repository\Repository;

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
        $locked = $this->composer->getLocker()->getLockedRepository()->findPackage($package->getName(), new MatchAllConstraint());

        // If we have installed version in the lock file, we will add the specific version as alias to the linked package.
        // This way we prevent conflicts with transitive dependencies.
        if (!is_null($locked)) {
            $aliasPackage = new AliasPackage($package, $locked->getVersion(), $rootPackage->getPrettyVersion());
        }

        $this->linkedRepository->addPackage($aliasPackage ?? $package);
        $this->requires[$package->getName()] = $package->createLink($rootPackage);
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

        // We need to remove dev-requires from the list of packages that are linked
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
        $installer->setUpdate(true)
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
