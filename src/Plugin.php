<?php declare(strict_types=1);

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
use ComposerLink\Repository\Repository;
use ComposerLink\Repository\Transformer;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class Plugin implements PluginInterface, Capable, EventSubscriberInterface
{
    protected ?IOInterface $io;

    protected Repository $repository;

    protected InstallationManager $installationManager;

    protected ComposerFileSystem $filesystem;

    protected LinkManager $linkedPackagesManager;

    protected LinkedPackageFactory $packageFactory;

    protected RepositoryManager $repositoryManager;

    public function __construct(ComposerFileSystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?: new ComposerFileSystem();
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        $io->debug("[ComposerLink]\tPlugin is deactivated");
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // TODO remove repository file and restore all packages
        $io->debug("[ComposerLink]\tPlugin uninstalling");
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        $io->debug("[ComposerLink]\tPlugin is activating");
        $this->io = $io;
        $this->installationManager = $composer->getInstallationManager();
        $this->repositoryManager = $composer->getRepositoryManager();

        $this->packageFactory = new LinkedPackageFactory(
            $this->installationManager,
            $this->repositoryManager->getLocalRepository()
        );

        $this->linkedPackagesManager = new LinkManager(
            $this->filesystem,
            $composer->getLoop(),
            $composer->getInstallationManager(),
            $this->repositoryManager->getLocalRepository()
        );

        // TODO use factory pattern
        $this->repository = new Repository(
            new Filesystem(new LocalFilesystemAdapter($composer->getConfig()->get('vendor-dir'))),
            $io,
            new Transformer()
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_UPDATE_CMD => [
                ['linkLinkedPackages']
            ],
        ];
    }

    public function getLinkManager(): LinkManager
    {
        return $this->linkedPackagesManager;
    }

    public function linkLinkedPackages(): void
    {
        foreach ($this->repository->all() as $linkedPackage) {
            if (!$this->linkedPackagesManager->isLinked($linkedPackage)) {
                // Package is updated, so we need to link the newer original package
                $oldOriginalPackage = $linkedPackage->getOriginalPackage();
                if (!is_null($oldOriginalPackage)) {
                    $newOriginalPackage = $this->repositoryManager
                        ->getLocalRepository()
                        ->findPackage($oldOriginalPackage->getName(), '*');
                    $linkedPackage->setOriginalPackage($newOriginalPackage);
                    $this->repository->store($linkedPackage);
                }

                $this->linkedPackagesManager->linkPackage($linkedPackage);
            }
        }

        $this->repository->persist();
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
}
