<?php

namespace ComposerLink;

use Composer\Composer;
use Composer\Downloader\DownloadManager;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Factory;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\Package;
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

        $this->repository = new LinkedPackagesRepository(
            new Filesystem(new LocalFilesystemAdapter(dirname(Factory::getComposerFile()))),
            $io
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_UPDATE_CMD => [
                ['linkLinkedPackages']
            ],
        ];
    }

    public function linkLinkedPackages(): void
    {
        $this->io->write('[ComposerLink] Loading linked packages');
        foreach ($this->repository->all() as $linkedPackage) {
            // Extract package information
            $package = $this->createPackageForPath($linkedPackage->getPath());
            $destination = $this->installationManager->getInstallPath($package);

            // Skip linking if already linked
            if ($this->filesystem->isSymlinkedDirectory($destination) ||
                $this->filesystem->isJunction($destination)
            ) {
                continue;
            }

            $this->filesystem->removeDirectory($destination);

            // Download the managed package from its path with the composer downloader
            $this->io->debug("[ComposerLink] Creating link to " . $linkedPackage->getPath() . " for package " . $linkedPackage->getName());
            $pathDownloader = $this->downloadManager->getDownloader('path');
            $pathDownloader->install($package, $destination);
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

    /**
     * Creates package from given path
     */
    private function createPackageForPath(string $path): Package
    {
        $json = (new JsonFile(
            realpath($path . DIRECTORY_SEPARATOR . 'composer.json')
        ))->read();
        $json['version'] = 'dev-master';

        // branch alias won't work, otherwise the ArrayLoader::load won't return an instance of CompletePackage
        unset($json['extra']['branch-alias']);

        $loader = new ArrayLoader();
        $package = $loader->load($json);
        $package->setDistUrl($path);

        return $package;
    }
}
