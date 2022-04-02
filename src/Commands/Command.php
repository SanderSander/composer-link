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

namespace ComposerLink\Commands;

use Composer\Command\BaseCommand;
use ComposerLink\Plugin;

abstract class Command extends BaseCommand
{
    protected Plugin $plugin;

    public function __construct(Plugin $plugin)
    {
        parent::__construct();

        $this->plugin = $plugin;
    }

    protected function isGlobal(): bool
    {
        return getcwd() === $this->getComposer()->getConfig()->get('home');
    }

    public function isAbsolutePath(string $file): bool
    {
        return '' !== $file && (
            strspn($file, '/\\', 0, 1)
                || (
                    strlen($file) > 3 && ctype_alpha($file[0])
                    && ':' === $file[1]
                    && strspn($file, '/\\', 2, 1)
                )
                || null !== parse_url($file, \PHP_URL_SCHEME)
        );
    }
}
