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

use ComposerLink\Package\LinkedPackageFactory;

class RepositoryFactory
{
    public function create(string $storageFile, LinkedPackageFactory $linkedPackageFactory): Repository
    {
        return new Repository(
            new JsonStorage($storageFile),
            new Transformer($linkedPackageFactory)
        );
    }
}
