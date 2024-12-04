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

namespace ComposerLink;

use Composer\Installer\InstallationManager;
use Composer\Json\JsonFile;
use Composer\Package\CompletePackage;
use Composer\Package\Loader\ArrayLoader;
use Composer\Repository\InstalledRepositoryInterface;
use ComposerLink\Package\LinkedCompletePackage;
use RuntimeException;

class LinkedPackageFactory
{
    public function __construct(
        protected readonly InstallationManager $installationManager,
        protected readonly InstalledRepositoryInterface $installedRepository,
    ) {
    }

    /**
     * @param non-empty-string $path
     */
    private function loadFromJsonFile(string $path): LinkedCompletePackage
    {
        if (!file_exists($path . DIRECTORY_SEPARATOR . 'composer.json')) {
            throw new RuntimeException(sprintf('No composer.json file found in "%s".', $path));
        }

        $json = (new JsonFile($path . DIRECTORY_SEPARATOR . 'composer.json'))->read();

        if (!is_array($json)) {
            throw new RuntimeException(sprintf('Unable to read composer.json in "%s"', $path));
        }

        $json['version'] = 'dev-linked';

        // branch alias won't work, otherwise the ArrayLoader::load won't return an instance of CompletePackage
        unset($json['extra']['branch-alias']);

        $loader = new ArrayLoader();
        /** @var CompletePackage $package */
        $package = $loader->load($json);

        return new LinkedCompletePackage($package, $path);
    }

    /**
     * @param non-empty-string $path
     */
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

        // TODO installation path exists only if package is installed
        //      we should add support when the package isn't required yet in composer.json
        /** @var string $destination */
        $destination = $this->installationManager->getInstallPath($newPackage);

        return new LinkedPackage(
            $path,
            $newPackage,
            $originalPackage,
            $destination
        );
    }
}
