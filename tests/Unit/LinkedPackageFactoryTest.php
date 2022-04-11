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

use Composer\Installer\InstallationManager;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use ComposerLink\LinkedPackageFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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

        static::assertSame('test/package', $result->getName());
        static::assertSame($originalPackage, $result->getOriginalPackage());
    }

    public function test_no_original_package(): void
    {
        $installationManager = $this->createMock(InstallationManager::class);
        $installedRepository = $this->createMock(InstalledRepositoryInterface::class);
        $installedRepository->method('getCanonicalPackages')->willReturn([]);

        $factory = new LinkedPackageFactory($installationManager, $installedRepository);
        $package = $factory->fromPath('tests/mock');
        static::assertNull($package->getOriginalPackage());
    }

    public function test_no_composer_file(): void
    {
        $installationManager = $this->createMock(InstallationManager::class);
        $installedRepository = $this->createMock(InstalledRepositoryInterface::class);
        $installedRepository->method('getCanonicalPackages')->willReturn([]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No composer.json file found in given path.');

        $factory = new LinkedPackageFactory($installationManager, $installedRepository);
        $factory->fromPath('tests/empty');
    }
}
