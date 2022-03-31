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

use Composer\Downloader\DownloadManager;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Composer\Util\Loop;

class LinkManager
{
    protected Filesystem $filesystem;

    protected IOInterface $io;

    protected DownloadManager $downloadManager;

    protected Loop $loop;

    public function __construct(
        Filesystem $filesystem,
        IOInterface $io,
        DownloadManager $downloadManager,
        Loop $loop
    ) {
        $this->filesystem = $filesystem;
        $this->io = $io;
        $this->downloadManager = $downloadManager;
        $this->loop = $loop;
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

        if (!is_null($linkedPackage->getOriginalPackage())) {
            $this->downloadManager->remove($linkedPackage->getOriginalPackage(), $linkedPackage->getInstallationPath());
        }

        $pathDownloader->prepare('path', $linkedPackage->getPackage(), $linkedPackage->getInstallationPath());
        $pathDownloader->install($linkedPackage->getPackage(), $linkedPackage->getInstallationPath());
        $pathDownloader->cleanup('path', $linkedPackage->getPackage(), $linkedPackage->getInstallationPath());
    }

    /**
     * Unlinks the package from the vendor directory
     */
    public function unlinkPackage(LinkedPackage $linkedPackage): void
    {
        // Remove linked package
        $pathDownloader = $this->downloadManager->getDownloader('path');
        $pathDownloader->remove($linkedPackage->getPackage(), $linkedPackage->getInstallationPath());

        if (!is_null($linkedPackage->getOriginalPackage())) {
            // Prepare (Not sure if really needed)
            $this->downloadManager->prepare(
                $linkedPackage->getOriginalPackage()->getType(),
                $linkedPackage->getOriginalPackage(),
                $linkedPackage->getInstallationPath()
            );

            // Download the original package
            $this->io->debug("[ComposerLink] Installing original package " . $linkedPackage->getOriginalPackage());
            $downloadPromise = $this->downloadManager->download(
                $linkedPackage->getOriginalPackage(),
                $linkedPackage->getInstallationPath()
            );
            $this->loop->wait([$downloadPromise]);

            // Install the original package
            $installPromise = $this->downloadManager->install(
                $linkedPackage->getOriginalPackage(),
                $linkedPackage->getInstallationPath()
            );

            $this->loop->wait([$installPromise]);
        }
    }
}
