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

use InvalidArgumentException;

class PathHelper
{
    /**
     * @param non-empty-string $path
     */
    public function __construct(
        protected readonly string $path,
    ) {
    }

    public function isWildCard(): bool
    {
        return substr($this->path, -2) === DIRECTORY_SEPARATOR . '*';
    }

    /**
     * @return PathHelper[]
     */
    public function getPathsFromWildcard(): array
    {
        /** @var list<string> $entries */
        $entries = glob($this->path, GLOB_ONLYDIR);
        $helpers = [];

        /** @var non-empty-string $entry */
        foreach ($entries as $entry) {
            if (!file_exists($entry . DIRECTORY_SEPARATOR . 'composer.json')) {
                continue;
            }

            $helpers[] = new PathHelper($entry);
        }

        return $helpers;
    }

    public function toAbsolutePath(string $workingDirectory): PathHelper
    {
        if ($this->isAbsolutePath($this->path)) {
            return $this;
        }

        $path = $this->isWildCard() ? substr($this->path, 0, -1) : $this->path;
        $real = realpath($workingDirectory . DIRECTORY_SEPARATOR . $path);

        if ($real === false) {
            throw new InvalidArgumentException(
                sprintf('Cannot resolve absolute path to %s from %s.', $path, $workingDirectory)
            );
        }

        if ($this->isWildCard()) {
            $real .= DIRECTORY_SEPARATOR . '*';
        }

        return new PathHelper($real);
    }

    /**
     * @return non-empty-string
     */
    public function getNormalizedPath(): string
    {
        /** @var non-empty-string $path */
        $path = $this->path;
        if (PHP_OS_FAMILY === 'Windows') {
            $path = str_replace('\\', '/', $path);
        }

        if (str_ends_with($path, '/')) {
            return substr($path, 0, -1);
        }

        return $path;
    }

    public function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/') || substr($path, 1, 1) === ':' || str_starts_with($path, '\\\\');
    }
}
