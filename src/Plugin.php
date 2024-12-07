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
use Throwable;

class Plugin implements PluginInterface, Capable, EventSubscriberInterface
{
    protected ?Repository $repository = null;

    protected ComposerFileSystem $filesystem;
    protected ?LinkedPackageFactory $packageFactory = null;

    protected Composer $composer;

    protected ?LinkManager $linkManager = null;

    protected RepositoryFactory $repositoryFactory;

    protected LinkManagerFactory $linkManagerFactory;

    /**
     * It can happen that activation doesn't work, this happens when this plugin is upgraded.
     * Composer runs this file through an eval() with renamed class names, but all other classes
     * in this library are still the old ones loaded in memory.
     *
     * We try to detect this, and skip the event callbacks if it happens
     */
    protected bool $couldNotActivate = false;

    public function __construct(
        ?ComposerFileSystem $filesystem = null,
        ?RepositoryFactory $repositoryFactory = null,
        ?LinkManagerFactory $linkManagerFactory = null,
    ) {
        $this->filesystem = $filesystem ?? new ComposerFileSystem();
        $this->repositoryFactory = $repositoryFactory ?? new RepositoryFactory();
        $this->linkManagerFactory = $linkManagerFactory ?? new LinkManagerFactory();
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
        try {
            $this->repository = $this->initializeRepository();
            $this->packageFactory = $this->initializeLinkedPackageFactory();
            $this->linkManager = $this->initializeLinkManager($io);
        } catch (Throwable $e) {
            $io->debug("[ComposerLink]\tException: " . $e->getMessage());
            $this->couldNotActivate = true;
        }
    }

    protected function initializeRepository(): Repository
    {
        $storageFile = $this->composer->getConfig()
                ->get('vendor-dir') . DIRECTORY_SEPARATOR . 'linked-packages.json';

        return $this->repositoryFactory->create($storageFile);
    }

    protected function initializeLinkedPackageFactory(): LinkedPackageFactory
    {
        return new LinkedPackageFactory(
            $this->composer->getInstallationManager(),
            $this->composer->getRepositoryManager()->getLocalRepository()
        );
    }

    protected function initializeLinkManager(IOInterface $io): LinkManager
    {
        return $this->linkManagerFactory->create(
            $this->getRepository(),
            new InstallerFactory($io, $this->composer),
            $io,
            $this->composer
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
        $linkManager = $this->getLinkManager();
        if ($linkManager->hasLinkedPackages()) {
            $linkManager->linkPackages($event->isDevMode());
        }
    }

    public function postUpdate(Event $event): void
    {
        // Plugin couldn't be activated probably because the plugin was updated
        if ($this->couldNotActivate) {
            $event->getIO()->warning('<warning>Composer link couldn\'t be activated because it was probably upgraded, run `composer install` again to link packages</warning>');

            return;
        }

        $linkManager = $this->getLinkManager();
        $repository = $this->getRepository();

        if ($linkManager->hasLinkedPackages()) {
            $localRepository = $this->composer->getRepositoryManager()->getLocalRepository();
            //  It can happen that a original package is updated,
            //  in those cases we need to update the state of the linked package by setting the original package
            foreach ($repository->all() as $package) {
                $original = $localRepository->findPackage($package->getName(), '*');
                $package->setOriginalPackage($original);
            }
            $repository->persist();

            $linkManager->linkPackages($event->isDevMode());
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
