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
use Composer\Installer;
use Composer\IO\IOInterface;

class InstallerFactory
{
    public function __construct(
        protected IOInterface $io,
        protected Composer $composer,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function create(): Installer
    {
        return Installer::create($this->io, $this->composer);
    }
}
