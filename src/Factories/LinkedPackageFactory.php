<?php

namespace ComposerLink\Factories;

use Composer\Json\JsonFile;
use Composer\Package\Loader\ArrayLoader;
use ComposerLink\Entities\LinkedPackage;

class LinkedPackageFactory
{
    public function fromPath(string $path): LinkedPackage
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

        return new LinkedPackage(
            $path,
            $package->getName()
        );
    }
}
