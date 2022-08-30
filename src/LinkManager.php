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

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Installer\InstallationManager;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;
use Composer\Util\Loop;
use Exception;
use React\Promise\PromiseInterface;

class LinkManager
{
    protected Filesystem $filesystem;

    protected Loop $loop;

    protected InstallationManager $installationManager;

    protected InstalledRepositoryInterface $installedRepository;

    public function __construct(
        Filesystem $filesystem,
        Loop $loop,
        InstallationManager $installationManager,
        InstalledRepositoryInterface $installedRepository,
        DependencyResolver $resolver
    ) {
        $this->filesystem = $filesystem;
        $this->loop = $loop;
        $this->installationManager = $installationManager;
        $this->installedRepository = $installedRepository;
        $this->resolver = $resolver;
    }

    /**
     * Checks if the given package is linked.
     */
    public function isLinked(LinkedPackage $linkedPackage): bool
    {
        return $this->filesystem->isSymlinkedDirectory($linkedPackage->getInstallationPath()) ||
        $this->filesystem->isJunction($linkedPackage->getInstallationPath());
    }

    /**
     * Links the package into the vendor directory.
     */
    public function linkPackage(LinkedPackage $linkedPackage): void
    {
        $operations = $this->resolver->resolveForPackage($linkedPackage);

        foreach ($operations as $operation) {
            if ($operation instanceof InstallOperation) {
                $this->install($operation->getPackage());
            }
        }
        /* ORIGINAL
        if (!is_null($linkedPackage->getOriginalPackage())) {
            $this->uninstall($linkedPackage->getOriginalPackage());
        }
        $this->install($linkedPackage->getPackage());
        */
    }

    /**
     * Unlinks the package from the vendor directory.
     */
    public function unlinkPackage(LinkedPackage $linkedPackage): void
    {
        // Update the repository to the current situation
        if (!is_null($linkedPackage->getOriginalPackage())) {
            $this->installedRepository->removePackage($linkedPackage->getOriginalPackage());
        }
        $this->installedRepository->addPackage($linkedPackage->getPackage());

        $this->uninstall($linkedPackage->getPackage());
        if (!is_null($linkedPackage->getOriginalPackage())) {
            $this->install($linkedPackage->getOriginalPackage());
        }
    }

    protected function uninstall(PackageInterface $package): void
    {
        $installer = $this->installationManager->getInstaller($package->getType());
        try {
            $this->wait($installer->uninstall($this->installedRepository, $package));
        } catch (Exception $exception) {
            $this->wait($installer->cleanup('uninstall', $package));
            throw $exception;
        }

        $this->wait($installer->cleanup('uninstall', $package));
    }

    /**
     * Downloads and installs the given package
     * https://github.com/composer/composer/blob/2.0.0/src/Composer/Util/SyncHelper.php.
     */
    protected function install(PackageInterface $package): void
    {
        $installer = $this->installationManager->getInstaller($package->getType());

        try {
            $this->wait($installer->download($package));
            $this->wait($installer->prepare('install', $package));
            $this->wait($installer->install($this->installedRepository, $package));
        } catch (Exception $exception) {
            $this->wait($installer->cleanup('install', $package));
            throw $exception;
        }

        $this->wait($installer->cleanup('install', $package));
    }

    /**
     * Waits for promise to be finished.
     */
    protected function wait(?PromiseInterface $promise): void
    {
        if (!is_null($promise)) {
            $this->loop->wait([$promise]);
        }
    }
}
