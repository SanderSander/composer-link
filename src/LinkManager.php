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
use Composer\Repository\ArrayRepository;
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
        $rootPackage = $this->composer->getPackage();

        // Load already linked packages
        foreach ($this->repository->all() as $package) {
            $this->linkedRepository->addPackage($package);
            $this->requires[$package->getName()] = $package->createLink($rootPackage);
        }
    }

    public function add(LinkedPackage $package): void
    {
        $rootPackage = $this->composer->getPackage();

        $this->repository->store($package);
        $this->repository->persist();

        if (!$this->linkedRepository->hasPackage($package)) {
            $this->linkedRepository->addPackage($package);
        }

        $this->requires[$package->getName()] = $package->createLink($rootPackage);
    }

    public function remove(LinkedPackage $package): void
    {
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
            ->setPlatformRequirementFilter(new IgnoreAllPlatformRequirementFilter())
            ->setUpdateAllowList(array_keys($this->requires))
            ->setDevMode($isDev)
            ->setUpdateAllowTransitiveDependencies(Request::UPDATE_LISTED_WITH_TRANSITIVE_DEPS_NO_ROOT_REQUIRE);

        $installer->run();

        $eventDispatcher->setRunScripts();
        $this->io->warning('<warning>Linking packages finished!</warning>');
    }
}
