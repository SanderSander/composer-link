<?php

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
use ComposerLink\Providers\CommandProvider;
use ComposerLink\Repositories\LinkedPackagesRepository;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class Plugin implements PluginInterface, Capable, EventSubscriberInterface
{
    protected ?IOInterface $io;

    protected LinkedPackagesRepository $repository;

    protected DownloadManager $downloadManager;

    protected InstallationManager $installationManager;

    protected \Composer\Util\Filesystem $filesystem;

    protected LinkedPackagesManager $linkedPackagesManager;

    public function __construct(\Composer\Util\Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?: new \Composer\Util\Filesystem();
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        $io->debug("[ComposerLink]\tPlugin is deactivated");
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        $io->debug("[ComposerLink]\tPlugin uninstalling");
    }

    public function activate(Composer $composer, IOInterface $io)
    {
        $io->debug("[ComposerLink]\tPlugin is activating");
        $this->io = $io;
        $this->downloadManager = $composer->getDownloadManager();
        $this->installationManager = $composer->getInstallationManager();

        $this->linkedPackagesManager = new LinkedPackagesManager(
            $this->filesystem,
            $this->io,
            $this->downloadManager
        );

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

    public function getLinkedPackagesManager(): LinkedPackagesManager
    {
        return $this->linkedPackagesManager;
    }

    public function linkLinkedPackages(): void
    {
        $this->io->write('[ComposerLink] Loading linked packages');
        foreach ($this->repository->all() as $linkedPackage) {
            if (!$this->linkedPackagesManager->isLinked($linkedPackage)) {
                $this->linkedPackagesManager->linkPackage($linkedPackage);
            }
        }
        $this->io->write('[ComposerLink] Done loading linked packages');
    }

    public function getCapabilities()
    {
        $this->io->debug("[ComposerLink]\tCapabilities are loaded");
        return [
            ComposerCommandProvider::class => CommandProvider::class,
        ];
    }

    public function getRepository(): LinkedPackagesRepository
    {
        return $this->repository;
    }
}
