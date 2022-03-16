<?php

namespace ComposerLink;

use Composer\Downloader\DownloadManager;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;

class LinkedPackagesManager
{
    protected Filesystem $filesystem;

    protected IOInterface $io;

    protected DownloadManager $downloadManager;

    public function __construct(
        Filesystem $filesystem,
        IOInterface $io,
        DownloadManager $downloadManager
    ) {
        $this->filesystem = $filesystem;
        $this->io = $io;
        $this->downloadManager = $downloadManager;
    }

    /**
     * Checks if the given package is linked
     */
    public function isLinked(LinkedPackage $linkedPackage): bool
    {
        return $this->filesystem->isSymlinkedDirectory($linkedPackage->getInstallationPath()) ||
        $this->filesystem->isJunction($linkedPackage->getInstallationPath());
    }

    /**
     * Links the package into the vendor directory
     */
    public function linkPackage(LinkedPackage $linkedPackage): void
    {
        $this->io->debug("[ComposerLink] Creating link to " . $linkedPackage->getPath() . " for package " . $linkedPackage->getPackage());
        $pathDownloader = $this->downloadManager->getDownloader('path');
        $pathDownloader->install($linkedPackage->getPackage(), $linkedPackage->getInstallationPath());
    }

    /**
     * Unlinks the package from the vendor directory
     */
    public function unlinkPackage(LinkedPackage $linkedPackage): void
    {
        $this->io->debug("[ComposerLink] Installing original package " . $linkedPackage->getPackage());
        $this->downloadManager->install($linkedPackage->getOriginalPackage(), $linkedPackage->getInstallationPath());
    }
}
