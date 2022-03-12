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

        if (is_null($originalPackage)) {
            throw new \RuntimeException('Original package not found, is it installed?');
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
