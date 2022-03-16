<?php

namespace ComposerLink\Factories;

use Composer\Installer\InstallationManager;
use Composer\Json\JsonFile;
use Composer\Package\CompletePackage;
use Composer\Package\Loader\ArrayLoader;
use Composer\Repository\InstalledRepositoryInterface;
use ComposerLink\LinkedPackage;

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
        $json = (new JsonFile(
            realpath($path . DIRECTORY_SEPARATOR . 'composer.json')
        ))->read();
        $json['version'] = 'dev-master';

        // branch alias won't work, otherwise the ArrayLoader::load won't return an instance of CompletePackage
        unset($json['extra']['branch-alias']);

        $loader = new ArrayLoader();
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
        $destination = $this->installationManager->getInstallPath($newPackage);

        return new LinkedPackage(
            $path,
            $newPackage,
            $originalPackage,
            $destination
        );
    }
}
