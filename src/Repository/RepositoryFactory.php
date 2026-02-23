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

use Composer\Composer;
use ComposerLink\Package\LinkedPackageFactory;
use ComposerLink\PathHelper;

class RepositoryFactory
{
    public function create(string $storageFile, LinkedPackageFactory $linkedPackageFactory, Composer $composer): Repository
    {
        $extra = $composer->getPackage()->getExtra();
        $paths = $extra['composer-link']['paths'] ?? [];
        foreach ($paths as $index => $path) {
            $paths[$index] = (new PathHelper($path))->getNormalizedPath();
        }

        return new Repository(
            new JsonStorage($storageFile),
            new Transformer($linkedPackageFactory),
            $paths,
        );
    }
}
