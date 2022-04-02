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

namespace ComposerLink\Repository;

use RuntimeException;

class JsonStorage implements StorageInterface
{
    protected string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function write(array $data): void
    {
        /** @var string $json */
        $json = json_encode($data);
        file_put_contents($this->file, $json);
    }

    public function read(): array
    {
        if (!$this->hasData()) {
            throw new RuntimeException('Cannot read data, no data stored.');
        }

        $data = file_get_contents($this->file);
        if ($data === false) {
            throw new RuntimeException('Cannot read data file.');
        }

        return json_decode($data, true);
    }

    public function hasData(): bool
    {
        return file_exists($this->file);
    }
}
