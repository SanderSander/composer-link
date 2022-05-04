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
use RuntimeException;

class PathHelper
{
    protected string $path;

    protected string $absolutePath;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function isWildCard(): bool
    {
        return substr($this->path, -2) === '/*';
    }

    /**
     * @return PathHelper[]
     */
    public function getPathsFromWildcard(): array
    {
        $path = substr($this->path, 0, -1);
        $paths = [];

        $entries = scandir($path);

        if ($entries === false) {
            throw new RuntimeException(sprintf('Cannot read directory "%s"', $this->path));
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (is_dir($path . $entry)) {
                $paths[] = new PathHelper($path . $entry);
            }
        }

        return $paths;
    }

    public function toAbsolutePath(string $workingDirectory): PathHelper
    {
        $path = $this->isWildCard() ? substr($this->path, -1) : $this->path;

        $real = realpath($workingDirectory . DIRECTORY_SEPARATOR . $path);
        if ($real === false) {
            throw new InvalidArgumentException(
                sprintf('Cannot resolve absolute path to %s.', $path)
            );
        }

        if ($this->isWildCard()) {
            $real .= DIRECTORY_SEPARATOR . '*';
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
