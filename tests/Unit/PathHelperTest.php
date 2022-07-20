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

namespace Tests\Unit;

use ComposerLink\PathHelper;
use InvalidArgumentException;

class PathHelperTest extends TestCase
{
    /**
     * @dataProvider provideAbsolutePaths
     */
    public function test_get_absolute_path(string $pah): void
    {
        $testPath = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..';
        $root = realpath($testPath);
        $helper = new PathHelper($pah);
        static::assertEquals(
            $root . DIRECTORY_SEPARATOR . $pah,
            $helper->toAbsolutePath($testPath)
                ->getNormalizedPath()
        );
    }

    public function test_absolute_path_to_absolute(): void
    {
        /** @var string $cwd */
        $cwd = getcwd();
        $pathWildcard = new PathHelper($this->tmpAbsoluteDir);
        $absolute = $pathWildcard->toAbsolutePath($cwd);

        // We expect a normalized path, so we remove the trailing slash
        static::assertSame(
            substr($this->tmpAbsoluteDir, 0, -1),
            $absolute->getNormalizedPath()
        );
    }

    public function test_get_invalid_absolute_path(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $helper = new PathHelper('some-path-non-existing-path');
        $root = PHP_OS_FAMILY === 'Windows' ? 'C:\\' : '/';
        $helper->toAbsolutePath($root);
    }

    public function test_paths_considered_equal_without_trailing_separator(): void
    {
        $path = PHP_OS_FAMILY === 'Windows' ? 'C:\\some\\path' : '/some/path';

        $helper1 = new PathHelper($path);
        $helper2 = new PathHelper($path . DIRECTORY_SEPARATOR);

        static::assertSame($helper1->getNormalizedPath(), $helper2->getNormalizedPath());
    }

    public function test_is_wildcard(): void
    {
        $pathWildcard = new PathHelper('..' . DIRECTORY_SEPARATOR . 'path' . DIRECTORY_SEPARATOR . '*');
        $pathNonWildcard = new PathHelper('..' . DIRECTORY_SEPARATOR . 'path');

        static::assertTrue($pathWildcard->isWildCard());
        static::assertFalse($pathNonWildcard->isWildCard());
    }

    public function test_get_paths_from_wildcard(): void
    {
        mkdir($this->tmpAbsoluteDir . 'test-1');
        touch($this->tmpAbsoluteDir . 'test-1' . DIRECTORY_SEPARATOR . 'composer.json');
        mkdir($this->tmpAbsoluteDir . 'test-2');
        touch($this->tmpAbsoluteDir . 'test-2' . DIRECTORY_SEPARATOR . 'composer.json');
        mkdir($this->tmpAbsoluteDir . 'test-3');

        $pathWildcard = new PathHelper($this->tmpAbsoluteDir . '*');
        static::assertCount(2, $pathWildcard->getPathsFromWildcard());
    }

    public function test_wildcard_path_to_wildcard_absolute(): void
    {
        /** @var string $cwd */
        $cwd = getcwd();
        $pathWildcard = new PathHelper($this->tmpRelativeDir . '*');
        $absolute = $pathWildcard->toAbsolutePath($cwd);

        static::assertTrue($absolute->isWildCard());
        static::assertSame($this->tmpAbsoluteDir . '*', $absolute->getNormalizedPath());
    }

    /**
     * @return string[][]
     */
    public function provideAbsolutePaths(): array
    {
        return [
            ['tests'],
            ['tests' . DIRECTORY_SEPARATOR . 'Unit' . DIRECTORY_SEPARATOR . 'TestCase.php'],
        ];
    }
}
