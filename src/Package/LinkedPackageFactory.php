<?php

declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Created by: Sander Visser <themastersleader@hotmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 *
 * @link https://github.com/SanderSander/composer-link
 */

namespace ComposerLink\Package;

use Composer\Installer\InstallationManager;
use Composer\Json\JsonFile;
use Composer\Package\CompletePackage;
use Composer\Package\Loader\ArrayLoader;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\LockArrayRepository;
use RuntimeException;

class LinkedPackageFactory
{
    public function __construct(
        protected readonly InstallationManager $installationManager,
        protected readonly LockArrayRepository $locked,
    ) {
    }

    /**
     * @param non-empty-string $path
     */
    private function loadFromJsonFile(string $path): CompletePackage
    {
        if (!file_exists($path . DIRECTORY_SEPARATOR . 'composer.json')) {
            throw new RuntimeException(sprintf('No composer.json file found in "%s".', $path));
        }

        $json = (new JsonFile($path . DIRECTORY_SEPARATOR . 'composer.json'))->read();

        if (!is_array($json)) {
            throw new RuntimeException(sprintf('Unable to read composer.json in "%s"', $path));
        }

        // Version is required here because we load it from a directory
        if (!isset($json['version'])) {
            $json['version'] = 'dev-linked';
        }

        /** @var CompletePackage $package */
        $package = (new ArrayLoader())->load($json);

        return $package;
    }

    /**
     * @param non-empty-string $path
     */
    public function fromPath(string $path): LinkedPackage
    {
        $originalPackage = null;
        $linkedPackage = $this->loadFromJsonFile($path);

        $packages = $this->locked->getCanonicalPackages();
        foreach ($packages as $package) {
            if ($package->getName() === $linkedPackage->getName()) {
                $originalPackage = $package;
            }
        }

        /** @var string $destination */
        $destination = $this->installationManager->getInstallPath($linkedPackage);

        return new LinkedPackage(
            $linkedPackage,
            $path,
            $destination,
            $originalPackage
        );
    }
}
