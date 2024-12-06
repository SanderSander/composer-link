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

use Composer\EventDispatcher\EventDispatcher;
use Composer\IO\IOInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\RepositoryManager;
use Composer\Util\Filesystem;
use ComposerLink\Repository\Repository;

class LinkManagerFactory
{
    public function create(Filesystem $filesystem,
        Repository $repository,
        InstallerFactory $installerFactory,
        IOInterface $io,
        EventDispatcher $eventDispatcher,
        RootPackageInterface $rootPackage,
        RepositoryManager $repositoryManager, ): LinkManager
    {
        return new LinkManager(
            $filesystem,
            $repository,
            $installerFactory,
            $io,
            $eventDispatcher,
            $rootPackage,
            $repositoryManager,
        );
    }
}
