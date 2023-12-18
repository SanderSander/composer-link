<?php

declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Copyright (c) 2021-2023 Sander Visser <themastersleader@hotmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 *
 * @link https://github.com/SanderSander/composer-link
 */

namespace ComposerLink;

use Composer\Package\CompletePackage;
use Composer\Package\PackageInterface;

class LinkedPackage
{
    public function __construct(
        protected readonly string $path,
        protected readonly CompletePackage $package,
        protected ?PackageInterface $originalPackage,
        protected readonly string $installationPath
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->package->getName();
    }

    public function getPackage(): CompletePackage
    {
        return $this->package;
    }

    public function getOriginalPackage(): ?PackageInterface
    {
        return $this->originalPackage;
    }

    public function getInstallationPath(): string
    {
        return $this->installationPath;
    }

    public function setOriginalPackage(?PackageInterface $package): void
    {
        $this->originalPackage = $package;
    }
}
