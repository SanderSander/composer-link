<?php declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Copyright (c) 2021-2022 Sander Visser <themastersleader@hotmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @link https://github.com/SanderSander/composer-link
 */

namespace ComposerLink;

use Composer\Composer;
use Composer\Downloader\DownloadManager;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem as ComposerFileSystem;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class Plugin implements PluginInterface, Capable, EventSubscriberInterface
{
    protected ?IOInterface $io;

    protected LinkedPackagesRepository $repository;

    protected DownloadManager $downloadManager;

    protected InstallationManager $installationManager;

    protected ComposerFileSystem $filesystem;

    protected LinkManager $linkedPackagesManager;

    protected LinkedPackageFactory $packageFactory;

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
        $this->downloadManager = $composer->getDownloadManager();
        $this->installationManager = $composer->getInstallationManager();

        $this->packageFactory = new LinkedPackageFactory(
            $this->installationManager,
            $composer->getRepositoryManager()->getLocalRepository()
        );

        $this->linkedPackagesManager = new LinkManager(
            $this->filesystem,
            $this->io,
            $this->downloadManager,
            $composer->getLoop()
        );

        // TODO use factory pattern
        $this->repository = new LinkedPackagesRepository(
            new Filesystem(new LocalFilesystemAdapter($composer->getConfig()->get('vendor-dir'))),
            $io
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
                $this->linkedPackagesManager->linkPackage($linkedPackage);
            }
        }
    }

    public function getCapabilities(): array
    {
        return [
            ComposerCommandProvider::class => CommandProvider::class,
        ];
    }

    public function getRepository(): LinkedPackagesRepository
    {
        return $this->repository;
    }

    public function getPackageFactory(): LinkedPackageFactory
    {
        return $this->packageFactory;
    }
}
