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
use Composer\Package\Link;
use Composer\Package\RootPackageInterface;
use Composer\Repository\ArrayRepository;
use Composer\Semver\Constraint\MatchAllConstraint;
use ComposerLink\Exceptions\PackageAlreadyLinked;
use ComposerLink\Package\LinkedPackage;
use ComposerLink\Package\LinkedPackageFactory;
use ComposerLink\Repository\Repository;
use Exception;
use RuntimeException;

class LinkManager
{
    protected readonly ArrayRepository $linkedRepository;

    /**
     * @var array<string, Link>
     */
    protected array $requires = [];

    /**
     * @var non-empty-string[]
     */
    protected array $extraPaths = [];

    protected RootPackageInterface $rootPackage;

    public function __construct(
        protected readonly Repository $repository,
        protected readonly InstallerFactory $installerFactory,
        protected readonly IOInterface $io,
        protected readonly Composer $composer,
        protected readonly LinkedPackageFactory $packageFactory,
    ) {
        $this->linkedRepository = new ArrayRepository();
        $this->rootPackage = $this->composer->getPackage();

        // Process data defined in the extra section of composer.json
        // We also store it in the repository, so that we know what was defined and if something was changes.
        if (isset($this->rootPackage->getExtra()['composer-link'])) {
            $extra = $this->rootPackage->getExtra()['composer-link'];

            if (isset($extra['paths'])) {
                foreach ($extra['paths'] as $path) {
                    $this->loadPackageFromExtra($path);
                }
            }

            // TODO maybe optimize this a bit and only save when dirty
            //$this->repository->setExtraPaths($this->extraPaths);
            $this->repository->persist();
        }

        // Load already linked packages
        foreach ($this->repository->all() as $package) {
            $this->linkedRepository->addPackage($package);
            $this->requires[$package->getName()] = $package->createLink($this->rootPackage);
        }
    }

    public function add(LinkedPackage $package): void
    {
        // Check if package is already linked
        /** @var LinkedPackage|null $existing */
        $existing = $this->linkedRepository->findPackage($package->getName(), new MatchAllConstraint());
        if (!is_null($existing)) {
            throw new PackageAlreadyLinked($package, $existing);
        }

        // Store package in our linked-packages.json
        $this->repository->store($package);
        $this->repository->persist();

        // Add to the in memory repository
        $this->linkedRepository->addPackage($package);

        // Add require
        // TODO really needed? we can build this on linking
        $rootPackage = $this->composer->getPackage();
        $this->requires[$package->getName()] = $package->createLink($rootPackage);
    }

    public function remove(LinkedPackage $package): void
    {
        if (!$this->linkedRepository->hasPackage($package)) {
            // TODO create exception
            throw new Exception('Not linked todo');
        }

        $this->linkedRepository->removePackage($package);
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

    /**
     * Load packages from the extra section, we add those as linked packages.
     * When these packages are unlinked, while defined in the extra section,
     * it will be registered and we skip those.
     *
     * @param non-empty-string $path
     */
    private function loadPackageFromExtra(string $path): void
    {
        $helper = new PathHelper($path);

        try {
            $package = $this->packageFactory->fromPath($helper->getNormalizedPath());
        } catch (RuntimeException) {
            // Unable to load package, but we will continue and warn the user.
            $this->io->writeError(sprintf(
                '<warning>Could not load linked package from "%s" defined in the extra section of composer.json.</warning>',
                $path
            ));

            return;
        }

        // Package was manually unlinked, so we ignore it
        if ($this->repository->hasUnlinkedFromExtra($package)) {
            // TODO should we inform the user?
            return;
        }


        // TODO with if --no-dev is set, but composer-link is installed globally
        $this->linkedRepository->addPackage($package);
        $this->requires[$package->getName()] = $package->createLink($this->rootPackage);
        $this->repository->addLinkedFromExtra($package);
    }
}
