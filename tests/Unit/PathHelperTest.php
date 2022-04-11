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
        $root = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..');
        $helper = new PathHelper($pah);
        static::assertEquals(
            $root . DIRECTORY_SEPARATOR . $pah,
            $helper->getAbsolutePath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..')
        );
    }

    public function test_get_invalid_absolute_path(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $helper = new PathHelper('some-path-non-existing-path');
        $helper->getAbsolutePath('/');
    }

    /**
     * @return string[][]
     */
    public function provideAbsolutePaths(): array
    {
        return [
            ['tests'],
            ['tests/Unit/TestCase.php'],
        ];
    }
}
