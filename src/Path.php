<?php declare(strict_types=1);

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

namespace ComposerLink;

use RuntimeException;

class Path
{
    protected string $path;

    protected string $absolutePath;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    protected function isAbsolutePath(): bool
    {
        return $this->path[0] === '/' || ($this->path[1] === ':' && ctype_alpha($this->path[0]));
    }

    public function getAbsolutePath(string $workingDirectory): string
    {
        $real = realpath($workingDirectory . DIRECTORY_SEPARATOR . $this->path);
        if ($real === false) {
            throw new RuntimeException('Cannot resolve absolute path.');
        }

        return $real;
    }
}
