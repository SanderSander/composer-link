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
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem as ComposerFileSystem;
use ComposerLink\Package\LinkedPackageFactory;
use ComposerLink\Repository\Repository;
use ComposerLink\Repository\RepositoryFactory;
use RuntimeException;

class Plugin implements PluginInterface, Capable, EventSubscriberInterface
{
    protected ?Repository $repository = null;

    protected ComposerFileSystem $filesystem;

    protected ?LinkManager $linkManager = null;

    protected ?LinkedPackageFactory $packageFactory = null;

    protected Composer $composer;

    public function __construct(
        ?ComposerFileSystem $filesystem = null,
        protected ?RepositoryFactory $repositoryFactory = null,
    ) {
        $this->filesystem = $filesystem ?? new ComposerFileSystem();
    }

    /**
     * {@inheritDoc}
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
        $io->debug("[ComposerLink]\tPlugin is deactivated");
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
        $io->debug("[ComposerLink]\tPlugin uninstalling");
    }

    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $io->debug("[ComposerLink]\tPlugin is activating");
        $this->composer = $composer;

        $this->initializeRepository();
        $this->initializeLinkedPackageFactory();
        $this->initializeLinkManager($io);
    }

    protected function initializeRepository(): void
    {
        $storageFile = $this->composer->getConfig()
                ->get('vendor-dir') . DIRECTORY_SEPARATOR . 'linked-packages.json';
        if (is_null($this->repositoryFactory)) {
            $this->repositoryFactory = new RepositoryFactory();
        }
        $this->repository = $this->repositoryFactory->create($storageFile);
    }

    protected function initializeLinkedPackageFactory(): void
    {
        $this->packageFactory = new LinkedPackageFactory(
            $this->composer->getInstallationManager(),
            $this->composer->getRepositoryManager()->getLocalRepository()
        );
    }

    protected function initializeLinkManager(IOInterface $io): void
    {
        if (is_null($this->repository)) {
            throw new RuntimeException('Repository not initialized');
        }

        $this->linkManager = new LinkManager(
            $this->filesystem,
            $this->repository,
            new InstallerFactory($io, $this->composer),
            $io,
            $this->composer->getEventDispatcher(),
            $this->composer->getPackage(),
            $this->composer->getRepositoryManager(),
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_UPDATE_CMD => [
                ['postUpdate'],
            ],
            ScriptEvents::POST_INSTALL_CMD => [
                ['postInstall'],
            ],
        ];
    }

    public function postInstall(Event $event): void
    {
        if (is_null($this->linkManager)) {
            throw new RuntimeException('Link manager not initialized');
        }

        if ($this->linkManager->hasLinkedPackages()) {
            $this->linkManager->linkPackages($event->isDevMode());
        }
    }

    public function postUpdate(Event $event): void
    {
        if (is_null($this->linkManager) || is_null($this->repository)) {
            throw new RuntimeException('Plugin not initialized');
        }

        if ($this->linkManager->hasLinkedPackages()) {

            $localRepository = $this->composer->getRepositoryManager()->getLocalRepository();

            //  It can happen that a original package is updated,
            //  in those cases we need to update the state of the linked package by setting the original package
            foreach ($this->repository->all() as $package) {
                $original = $localRepository->findPackage($package->getName(), '*');
                $package->setOriginalPackage($original);
            }
            $this->repository->persist();

            $this->linkManager->linkPackages($event->isDevMode());
        }
    }

    public function getLinkManager(): LinkManager
    {
        if (is_null($this->linkManager)) {
            throw new RuntimeException('Plugin not activated');
        }

        return $this->linkManager;
    }

    public function getCapabilities(): array
    {
        return [
            ComposerCommandProvider::class => CommandProvider::class,
        ];
    }

    public function getRepository(): Repository
    {
        if (is_null($this->repository)) {
            throw new RuntimeException('Plugin not activated');
        }

        return $this->repository;
    }

    public function getPackageFactory(): LinkedPackageFactory
    {
        if (is_null($this->packageFactory)) {
            throw new RuntimeException('Plugin not activated');
        }

        return $this->packageFactory;
    }

    /**
     * Check if this plugin is running from global or local project.
     */
    public function isGlobal(): bool
    {
        return getcwd() === $this->composer->getConfig()->get('home');
    }
}
