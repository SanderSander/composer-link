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
use ComposerLink\Actions\LinkPackages;
use ComposerLink\Repository\Repository;
use ComposerLink\Repository\RepositoryFactory;
use RuntimeException;
use Throwable;

class Plugin implements PluginInterface, Capable, EventSubscriberInterface
{
    protected ?Repository $repository = null;

    protected ComposerFileSystem $filesystem;

    protected ?LinkManager $linkManager = null;

    protected ?LinkedPackageFactory $packageFactory = null;

    protected Composer $composer;

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
        protected ?LinkPackages $linkPackages = null,
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
        // TODO remove repository file and restore all packages
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
            $this->initializeRepository();
            $this->initializeLinkedPackageFactory();
            $this->initializeLinkManager();
            $this->initializeLinkPackages();
        } catch (Throwable $e) {
            $io->debug("[ComposerLink]\tException: " . $e->getMessage());
            $this->couldNotActivate = true;
        }
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

    protected function initializeLinkManager(): void
    {
        $this->linkManager = new LinkManager(
            $this->filesystem,
            $this->composer->getLoop(),
            $this->composer->getInstallationManager(),
            $this->composer->getRepositoryManager()->getLocalRepository()
        );
    }

    protected function initializeLinkPackages(): void
    {
        if (is_null($this->linkPackages)) {
            $this->linkPackages = new LinkPackages(
                $this->getLinkManager(),
                $this->getRepository(),
                $this->composer->getRepositoryManager()
            );
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_UPDATE_CMD => [
                ['linkLinkedPackages'],
            ],
        ];
    }

    public function getLinkManager(): LinkManager
    {
        if (is_null($this->linkManager)) {
            throw new RuntimeException('Plugin not activated');
        }

        return $this->linkManager;
    }

    public function linkLinkedPackages(Event $event): void
    {
        // Plugin couldn't be activated probably because the plugin was updated
        if ($this->couldNotActivate) {
            $event->getIO()->warning('<warning>Composer link couldn\'t be activated because it was probably upgraded, run `composer install` again to link packages</warning>');

            return;
        }

        if (is_null($this->linkPackages)) {
            throw new RuntimeException('Plugin not activated');
        }
        $this->linkPackages->execute();
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
