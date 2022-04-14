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

namespace ComposerLink\Repository;

use Composer\Package\CompletePackage;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\Loader\ArrayLoader;
use ComposerLink\LinkedPackage;

class Transformer
{
    protected ArrayLoader $composerLoader;

    protected ArrayDumper $composerDumper;

    public function __construct()
    {
        $this->composerLoader = new ArrayLoader();
        $this->composerDumper = new ArrayDumper();
    }

    /**
     * Load a Linked package from array data.
     *
     * @param array<string, mixed> $data
     */
    public function load(array $data): LinkedPackage
    {
        /** @var CompletePackage $newPackage */
        $newPackage = $this->composerLoader->load($data['package']);
        $originalPackage = isset($data['originalPackage']) ?
            $this->composerLoader->load($data['originalPackage']) : null;

        return new LinkedPackage(
            $data['path'],
            $newPackage,
            $originalPackage,
            $data['installationPath']
        );
    }

    /**
     * Export LinkedPackage to array data.
     *
     * @return array<string, mixed>
     */
    public function export(LinkedPackage $package): array
    {
        $data = [];
        $data['path'] = $package->getPath();
        $data['installationPath'] = $package->getInstallationPath();
        $data['package'] = $this->composerDumper->dump($package->getPackage());
        if (!is_null($package->getOriginalPackage())) {
            $data['originalPackage'] = $this->composerDumper->dump($package->getOriginalPackage());
        }

        return $data;
    }
}
