<?php

declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Copyright (c) 2021-2022 Sander Visser <themastersleader@hotmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 *
 * @link https://github.com/SanderSander/composer-link
 */

namespace ComposerLink;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Repository\RepositoryManager;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem as ComposerFileSystem;
use ComposerLink\Actions\LinkPackages;
use ComposerLink\Repository\JsonStorage;
use ComposerLink\Repository\Repository;
use ComposerLink\Repository\Transformer;
use RuntimeException;

class Plugin implements PluginInterface, Capable, EventSubscriberInterface
{
    protected ?IOInterface $io;

    protected Repository $repository;

    protected InstallationManager $installationManager;

    protected ComposerFileSystem $filesystem;

    protected LinkManager $linkManager;

    protected LinkedPackageFactory $packageFactory;

    protected RepositoryManager $repositoryManager;

    protected Composer $composer;

    protected ?LinkPackages $linkPackages;

    public function __construct(ComposerFileSystem $filesystem = null, LinkPackages $linkPackages = null)
    {
        $this->filesystem = $filesystem ?? new ComposerFileSystem();
        $this->linkPackages = $linkPackages;
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
        $io->debug("[ComposerLink]\tPlugin is deactivated");
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
        // TODO remove repository file and restore all packages
        $io->debug("[ComposerLink]\tPlugin uninstalling");
    }

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $io->debug("[ComposerLink]\tPlugin is activating");
        $this->io = $io;
        $this->installationManager = $composer->getInstallationManager();
        $this->repositoryManager = $composer->getRepositoryManager();
        $this->composer = $composer;

        $this->initializeProperties();

        $storageFile = $composer->getConfig()->get('vendor-dir') . DIRECTORY_SEPARATOR . 'linked-packages.json';
        $this->repository = new Repository(
            new JsonStorage($storageFile),
            $io,
            new Transformer()
        );
    }

    /**
     * We can't do this in the constructor, would be nice to use some sort of container for this.
     */
    protected function initializeProperties(): void
    {
        $this->packageFactory = new LinkedPackageFactory(
            $this->installationManager,
            $this->repositoryManager->getLocalRepository()
        );

        $this->linkManager = new LinkManager(
            $this->filesystem,
            $this->composer->getLoop(),
            $this->composer->getInstallationManager(),
            $this->repositoryManager->getLocalRepository()
        );

        if (!is_null($this->linkPackages)) {
            $this->linkPackages = new LinkPackages(
                $this->linkManager,
                $this->repository,
                $this->repositoryManager
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
        return $this->linkManager;
    }

    public function linkLinkedPackages(): void
    {
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
        return $this->repository;
    }

    public function getPackageFactory(): LinkedPackageFactory
    {
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
