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

use Composer\Installer\InstallationManager;
use Composer\Json\JsonFile;
use Composer\Package\CompletePackage;
use Composer\Package\Loader\ArrayLoader;
use Composer\Repository\InstalledRepositoryInterface;

class LinkedPackageFactory
{
    protected InstallationManager $installationManager;

    protected InstalledRepositoryInterface $installedRepository;

    public function __construct(InstallationManager $installationManager, InstalledRepositoryInterface $installedRepository)
    {
        $this->installationManager = $installationManager;
        $this->installedRepository = $installedRepository;
    }

    private function loadFromJsonFile(string $path): CompletePackage
    {
        $realPath = realpath($path . DIRECTORY_SEPARATOR . 'composer.json');
        if ($realPath === false) {
            throw new \RuntimeException('No composer.json file found in given path.');
        }

        $json = (new JsonFile($realPath))->read();
        $json['version'] = 'dev-master';

        // branch alias won't work, otherwise the ArrayLoader::load won't return an instance of CompletePackage
        unset($json['extra']['branch-alias']);

        $loader = new ArrayLoader();
        /** @var CompletePackage $package */
        $package = $loader->load($json);
        $package->setDistUrl($path);
        $package->setInstallationSource('dist');
        $package->setDistType('path');

        return $package;
    }

    public function fromPath(string $path): LinkedPackage
    {
        $originalPackage = null;
        $newPackage = $this->loadFromJsonFile($path);
        $packages = $this->installedRepository->getCanonicalPackages();
        foreach ($packages as $package) {
            if ($package->getName() === $newPackage->getName()) {
                $originalPackage = $package;
            }
        }

        $destination = $this->installationManager->getInstallPath($newPackage);
        return new LinkedPackage(
            $path,
            $newPackage,
            $originalPackage,
            $destination
        );
    }
}
