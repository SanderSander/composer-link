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

namespace ComposerLink\Repository;

use Composer\Package\CompletePackage;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\Loader\ArrayLoader;
use ComposerLink\Package\LinkedPackage;

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
        /** @var CompletePackage $completePackage */
        $completePackage = $this->composerLoader->load($data['package']);
        $originalPackage = isset($data['originalPackage']) ?
            $this->composerLoader->load($data['originalPackage']) : null;

        $linkedPackage = new LinkedPackage(
            $completePackage,
            $data['path'],
            $data['installationPath'],
            $originalPackage,
        );
        $linkedPackage->setWithoutDependencies($data['withoutDependencies'] ?? true);

        return $linkedPackage;
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
        $data['package'] = $this->composerDumper->dump($package->getLinkedPackage());
        if (!is_null($package->getOriginalPackage())) {
            $data['originalPackage'] = $this->composerDumper->dump($package->getOriginalPackage());
        }
        $data['withoutDependencies'] = $package->isWithoutDependencies();

        return $data;
    }
}
