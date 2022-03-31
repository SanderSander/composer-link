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

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\Downloader\DownloadManager;
use Composer\Installer\InstallationManager;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;
use Composer\Util\Loop;

class LinkManager
{
    protected Filesystem $filesystem;

    protected DownloadManager $downloadManager;

    protected Loop $loop;

    protected InstallationManager $installationManager;

    protected InstalledRepositoryInterface $installedRepository;

    public function __construct(
        Filesystem $filesystem,
        DownloadManager $downloadManager,
        Loop $loop,
        InstallationManager $installationManager,
        InstalledRepositoryInterface  $installedRepository
    ) {
        $this->filesystem = $filesystem;
        $this->downloadManager = $downloadManager;
        $this->loop = $loop;
        $this->installationManager = $installationManager;
        $this->installedRepository = $installedRepository;
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
        if (!is_null($linkedPackage->getOriginalPackage())) {
            $this->uninstall($linkedPackage->getOriginalPackage());
        }

        $this->install($linkedPackage->getPackage());
    }

    /**
     * Unlinks the package from the vendor directory
     */
    public function unlinkPackage(LinkedPackage $linkedPackage): void
    {
        // Update the repository to the current situation
        if (!is_null($linkedPackage->getOriginalPackage())) {
            $this->installedRepository->removePackage($linkedPackage->getOriginalPackage());
        }
        $this->installedRepository->addPackage($linkedPackage->getPackage());

        // Uninstall the linked package
        $this->uninstall($linkedPackage->getPackage());

        // Reinstall the linked package
        if (!is_null($linkedPackage->getOriginalPackage())) {
            // Prepare (Not sure if really needed)
            $this->downloadManager->prepare(
                $linkedPackage->getOriginalPackage()->getType(),
                $linkedPackage->getOriginalPackage(),
                $linkedPackage->getInstallationPath()
            );

            // Download the original package
            $downloadPromise = $this->downloadManager->download(
                $linkedPackage->getOriginalPackage(),
                $linkedPackage->getInstallationPath()
            );
            $this->loop->wait([$downloadPromise]);

            $this->install($linkedPackage->getOriginalPackage());
        }
    }

    protected function uninstall(PackageInterface $package): void
    {
        $promise = $this->installationManager->uninstall(
            $this->installedRepository,
            new UninstallOperation($package)
        );

        if (!is_null($promise)) {
            $this->loop->wait([$promise]);
        }
    }

    protected function install(PackageInterface $package): void
    {
        $promise = $this->installationManager->install(
            $this->installedRepository,
            new InstallOperation($package)
        );

        if (!is_null($promise)) {
            $this->loop->wait([$promise]);
        }
    }
}
