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

    public function test_get_invalid_absolute_path(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $helper = new PathHelper('some-path-non-existing-path');
        $root = PHP_OS_FAMILY === 'Windows' ? 'C:\\' : '/';
        $helper->toAbsolutePath($root);
    }

    public function test_paths_considered_equal_without_trailing_separator(): void
    {
        $helper1 = new PathHelper('/some/path');
        $helper2 = new PathHelper('/some/path');

        static::assertSame($helper1->getNormalizedPath(), $helper2->getNormalizedPath());
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
