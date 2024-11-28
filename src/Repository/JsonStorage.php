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

namespace ComposerLink\Repository;

use RuntimeException;

class JsonStorage implements StorageInterface
{
    public function __construct(
        protected readonly string $file,
    ) {
    }

    public function write(array $data): void
    {
        $json = json_encode($data);
        file_put_contents($this->file, $json);
    }

    public function read(): array
    {
        if (!$this->hasData()) {
            throw new RuntimeException('Cannot read data, no data stored.');
        }

        /** @var string $data */
        $data = file_get_contents($this->file);

        return json_decode($data, true);
    }

    public function hasData(): bool
    {
        return file_exists($this->file);
    }
}
