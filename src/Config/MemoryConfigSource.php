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

namespace ComposerLink\Config;

use Composer\Config\ConfigSourceInterface;

class MemoryConfigSource implements ConfigSourceInterface
{
    public function addRepository(string $name, $config, bool $append = true): void
    {
        // TODO: Implement addRepository() method.
    }

    public function removeRepository(string $name): void
    {
        // TODO: Implement removeRepository() method.
    }

    public function addConfigSetting(string $name, $value): void
    {
        // TODO: Implement addConfigSetting() method.
    }

    public function removeConfigSetting(string $name): void
    {
        // TODO: Implement removeConfigSetting() method.
    }

    public function addProperty(string $name, $value): void
    {
        // TODO: Implement addProperty() method.
    }

    public function removeProperty(string $name): void
    {
        // TODO: Implement removeProperty() method.
    }

    public function addLink(string $type, string $name, string $value): void
    {
        // TODO: Implement addLink() method.
    }

    public function removeLink(string $type, string $name): void
    {
        // TODO: Implement removeLink() method.
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }
}