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

namespace Tests\Unit\Repository;

use ComposerLink\Repository\JsonStorage;
use RuntimeException;
use Tests\Unit\TestCase;

class JsonStorageTest extends TestCase
{
    public function test_throws_exception_when_not_data_available(): void
    {
        $this->expectException(RuntimeException::class);
        self::expectExceptionMessage('Cannot read data, no data stored.');

        $storage = new JsonStorage($this->rootDir . 'test.json');
        $storage->read();
    }

    public function test_can_write_and_read(): void
    {
        $storage = new JsonStorage($this->rootDir . 'test.json');
        $storage->write(['test' => 'data']);
        static::assertEquals(['test' => 'data'], $storage->read());
    }
}
