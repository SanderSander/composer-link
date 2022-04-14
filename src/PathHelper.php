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

namespace ComposerLink;

use InvalidArgumentException;

class PathHelper
{
    protected string $path;

    protected string $absolutePath;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function toAbsolutePath(string $workingDirectory): PathHelper
    {
        $real = realpath($workingDirectory . DIRECTORY_SEPARATOR . $this->path);
        if ($real === false) {
            throw new InvalidArgumentException(
                sprintf('Cannot resolve absolute path to %s.', $this->path)
            );
        }

        return new PathHelper($real);
    }

    public function getNormalizedPath(): string
    {
        if (substr($this->path, -1) === DIRECTORY_SEPARATOR) {
            return substr($this->path, 0, -1);
        }

        return $this->path;
    }
}
