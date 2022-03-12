<?php declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Copyright (c) 2021-2022 Sander Visser <themastersleader@hotmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @link https://github.com/SanderSander/composer-link
 */

namespace ComposerLink;

use Composer\Package\CompletePackage;
use Composer\Package\PackageInterface;

class LinkedPackage
{
    protected string $path;

    protected CompletePackage $package;

    protected PackageInterface $originalPackage;

    protected string $installationPath;

    public function __construct(
        string $path,
        CompletePackage $package,
        PackageInterface $originalPackage,
        string $installationPath
    ) {
        $this->path = $path;
        $this->package = $package;
        $this->originalPackage = $originalPackage;
        $this->installationPath = $installationPath;
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

    public function getOriginalPackage(): PackageInterface
    {
        return $this->originalPackage;
    }

    public function getInstallationPath(): string
    {
        return $this->installationPath;
    }
}
