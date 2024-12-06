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

use Composer\DependencyResolver\Request;
use Composer\EventDispatcher\EventDispatcher;
use Composer\IO\IOInterface;
use Composer\Package\Link;
use Composer\Package\RootPackageInterface;
use Composer\Repository\ArrayRepository;
use Composer\Repository\RepositoryManager;
use Composer\Util\Filesystem;
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
        protected readonly Filesystem $filesystem,
        protected readonly Repository $repository,
        protected readonly InstallerFactory $installerFactory,
        protected readonly IOInterface $io,
        protected readonly EventDispatcher $eventDispatcher,
        protected readonly RootPackageInterface $rootPackage,
        protected readonly RepositoryManager $repositoryManager,
    ) {
        $this->linkedRepository = new ArrayRepository();

        // Load already linked packages
        foreach ($this->repository->all() as $package) {
            $this->linkedRepository->addPackage($package);
            $this->requires[$package->getName()] = $package->createLink($this->rootPackage);
        }
    }

    public function add(LinkedPackage $package): void
    {
        $this->repository->store($package);
        $this->repository->persist();

        if (!$this->linkedRepository->hasPackage($package)) {
            $this->linkedRepository->addPackage($package);
        }

        $this->requires[$package->getName()] = $package->createLink($this->rootPackage);
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
        // Use the composer installer to install the linked packages with dependencies
        $this->repositoryManager->prependRepository($this->linkedRepository);

        // Add requirement to the current/loaded composer.json
        $this->rootPackage->setRequires(array_merge($this->rootPackage->getRequires(), $this->requires));
        $this->io->warning('<warning>Linking packages, Lock file will be generated in memory but not written to disk.</warning>');

        // We need to remove dev-requires from the list of packages that are linked
        $devRequires = $this->rootPackage->getDevRequires();
        foreach ($this->linkedRepository->getPackages() as $package) {
            unset($devRequires[$package->getName()]);
        }
        $this->rootPackage->setDevRequires($devRequires);

        // Prevent circular call to script handler 'post-update-cmd' by creating a new composer instance
        // We also need to set this on the Installer while it's deprecated
        $this->eventDispatcher->setRunScripts(false);

        $installer = $this->installerFactory->create() /* @phpstan-ignore method.deprecated */
            ->setUpdate(true)
            ->setInstall(true)
            ->setWriteLock(false)
            ->setRunScripts(false)
            ->setUpdateAllowList(array_keys($this->requires))
            ->setDevMode($isDev)
            ->setUpdateAllowTransitiveDependencies(Request::UPDATE_ONLY_LISTED);
        $installer->run();

        $this->eventDispatcher->setRunScripts();
        $this->io->warning('<warning>Linking packages finished!</warning>');
    }
}
