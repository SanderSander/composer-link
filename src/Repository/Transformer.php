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

use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\Loader\ArrayLoader;
use ComposerLink\Package\LinkedPackage;
use ComposerLink\Package\LinkedPackageFactory;

class Transformer
{
    protected ArrayLoader $composerLoader;

    protected ArrayDumper $composerDumper;

    public function __construct(
        protected LinkedPackageFactory $linkedPackageFactory,
    ) {
        $this->composerLoader = new ArrayLoader();
        $this->composerDumper = new ArrayDumper();
    }

    /**
     * Load a Linked package from array data.
     *
     * @param array{path: non-empty-string, withoutDependencies?: bool} $data
     */
    public function load(array $data): LinkedPackage
    {
        // Load from the path again since the composer.json can be changed
        $linkedPackage = $this->linkedPackageFactory->fromPath($data['path']);
        $linkedPackage->setWithoutDependencies($data['withoutDependencies'] ?? true);

        return $linkedPackage;
    }

    /**
     * Export LinkedPackage to array data.
     *
     * @return array{
     *     path: non-empty-string,
     *     withoutDependencies: bool
     * }
     */
    public function export(LinkedPackage $package): array
    {
        $data = [];
        $data['path'] = $package->getPath();
        $data['withoutDependencies'] = $package->isWithoutDependencies();

        return $data;
    }
}
