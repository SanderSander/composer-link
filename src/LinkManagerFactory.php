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

use Composer\Composer;
use Composer\IO\IOInterface;
use ComposerLink\Repository\Repository;

class LinkManagerFactory
{
    public function create(
        Repository $repository,
        InstallerFactory $installerFactory,
        IOInterface $io,
        Composer $composer): LinkManager
    {
        return new LinkManager(
            $repository,
            $installerFactory,
            $io,
            $composer
        );
    }
}
