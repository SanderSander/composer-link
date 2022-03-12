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

namespace Tests\Unit;

use Composer\Installer\InstallationManager;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use ComposerLink\LinkedPackage;
use ComposerLink\LinkedPackageFactory;
use PHPUnit\Framework\TestCase;

class LinkedPackageFactoryTest extends TestCase
{
    public function test_factory(): void
    {
        $installationManager = $this->createMock(InstallationManager::class);
        $installedRepository = $this->createMock(InstalledRepositoryInterface::class);
        $originalPackage = $this->createMock(PackageInterface::class);
        $originalPackage->method('getName')->willReturn('test/package');
        $installedRepository->method('getCanonicalPackages')->willReturn([$originalPackage]);

        $factory = new LinkedPackageFactory($installationManager, $installedRepository);
        $result = $factory->fromPath('tests/mock');

        $this->assertInstanceOf(LinkedPackage::class, $result);
    }

    public function test_no_original_package(): void
    {
        $installationManager = $this->createMock(InstallationManager::class);
        $installedRepository = $this->createMock(InstalledRepositoryInterface::class);
        $installedRepository->method('getCanonicalPackages')->willReturn([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Original package not found, is it installed?');

        $factory = new LinkedPackageFactory($installationManager, $installedRepository);
        $factory->fromPath('tests/mock');
    }

    public function test_no_composer_file(): void
    {
        $installationManager = $this->createMock(InstallationManager::class);
        $installedRepository = $this->createMock(InstalledRepositoryInterface::class);
        $installedRepository->method('getCanonicalPackages')->willReturn([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No composer.json file found in given path.');

        $factory = new LinkedPackageFactory($installationManager, $installedRepository);
        $factory->fromPath('tests/empty');
    }
}
