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
use Composer\DependencyResolver\DefaultPolicy;
use Composer\DependencyResolver\Pool;
use Composer\DependencyResolver\Request;
use Composer\DependencyResolver\Solver;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
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
    private Composer $composer;
    private IOInterface $io;

    public function __construct(
        Filesystem $filesystem,
        Loop $loop,
        InstallationManager $installationManager,
        InstalledRepositoryInterface $installedRepository,
        Composer $composer,
        IOInterface $io
    ) {
        $this->filesystem = $filesystem;
        $this->loop = $loop;
        $this->installationManager = $installationManager;
        $this->installedRepository = $installedRepository;
        $this->composer = $composer;
        $this->io = $io;
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
        var_dump(count($this->installedRepository->getPackages()));
        if (!is_null($linkedPackage->getOriginalPackage())) {
            $this->uninstall($linkedPackage->getOriginalPackage());
            $leftOvers = $this->installedRepository->search($linkedPackage->getOriginalPackage()->getName());

            // It could happen that we have alias left-overs here.
            var_dump($leftOvers);
        }



        $this->install($linkedPackage->getPackage());

        $this->io->warning('----------------------');
        var_dump(count($this->installedRepository->getPackages()));
        $policy = new DefaultPolicy();
        $pool = new Pool($this->installedRepository->getPackages());
        $request = new Request($this->composer->getLocker()->getLockedRepository());
        foreach ($pool->getPackages() as $package) {
            $request->lockPackage($package);
        }
        //$request->fixLockedPackage($linkedPackage->getPackage());
        $this->io->write($pool->__toString());
        $solver = new Solver($policy, $pool, $this->io);
        $transaction = $solver->solve($request);
        $operations = $transaction->getOperations();
        foreach ($operations as $operation) {
            $this->io->write($operation->show(false));
        }
        $this->io->warning('----------------------');

        $this->install($linkedPackage->getPackage());
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
