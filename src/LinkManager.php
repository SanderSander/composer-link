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

        $this->downloadManager->remove($linkedPackage->getOriginalPackage(), $linkedPackage->getInstallationPath());

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
