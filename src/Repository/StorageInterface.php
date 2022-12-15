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

namespace ComposerLink\Repository;

interface StorageInterface
{
    /**
     * Check if storage has data stored.
     */
    public function hasData(): bool;

    /**
     * Read data from storage.
     *
     * @return array<string, mixed>
     */
    public function read(): array;

    /**
     * Write data to storage.
     *
     * @param array<string, mixed> $data
     */
    public function write(array $data): void;
}
