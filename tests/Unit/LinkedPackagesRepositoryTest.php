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

namespace Unit;

use Composer\IO\IOInterface;
use ComposerLink\LinkedPackage;
use ComposerLink\LinkedPackagesRepository;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;

class LinkedPackagesRepositoryTest extends TestCase
{
    public function test_if_package_is_stored_and_persisted(): void
    {
        $io = $this->createStub(IOInterface::class);
        $package = $this->createStub(LinkedPackage::class);
        $fileSystem = $this->createMock(FilesystemOperator::class);

        $repository = new LinkedPackagesRepository(
            $fileSystem,
            $io
        );

        $repository->store($package);
        $this->assertCount(1, $repository->all());
        $this->assertSame($package, $repository->all()[0]);

        $fileSystem->expects($this->once())
            ->method('write')
            ->with('link.dat', serialize([$package]));
        $repository->persist();
    }

    public function test_find_by_path(): void
    {
        $io = $this->createStub(IOInterface::class);
        $package = $this->createMock(LinkedPackage::class);
        $package->method('getPath')->willReturn('/test-path');
        $fileSystem = $this->createMock(FilesystemOperator::class);

        $repository = new LinkedPackagesRepository(
            $fileSystem,
            $io
        );

        $repository->store($package);
        $this->assertSame($package, $repository->findByPath('/test-path'));
        $this->assertNull($repository->findByPath('/test-path-other'));
    }

    public function test_find_by_name(): void
    {
        $io = $this->createStub(IOInterface::class);
        $package = $this->createMock(LinkedPackage::class);
        $package->method('getName')->willReturn('test/package');
        $fileSystem = $this->createMock(FilesystemOperator::class);

        $repository = new LinkedPackagesRepository(
            $fileSystem,
            $io
        );

        $repository->store($package);
        $this->assertSame($package, $repository->findByName('test/package'));
        $this->assertNull($repository->findByName('test/package-other'));
    }

    public function test_package_is_removed(): void
    {
        $io = $this->createStub(IOInterface::class);
        $package = $this->createMock(LinkedPackage::class);
        $fileSystem = $this->createMock(FilesystemOperator::class);

        $repository = new LinkedPackagesRepository(
            $fileSystem,
            $io
        );

        $repository->store($package);
        $repository->remove($package);

        $this->assertCount(0, $repository->all());

        $fileSystem->expects($this->once())
            ->method('write')
            ->with('link.dat', serialize([]));
        $repository->persist();
    }

    public function test_remove_throws_exception(): void
    {
        $io = $this->createStub(IOInterface::class);
        $package = $this->createMock(LinkedPackage::class);
        $fileSystem = $this->createMock(FilesystemOperator::class);

        $repository = new LinkedPackagesRepository(
            $fileSystem,
            $io
        );

        $this->expectException(\RuntimeException::class);
        $repository->remove($package);
    }

    public function test_if_data_can_be_loaded_from_file(): void
    {
        $io = $this->createStub(IOInterface::class);
        $package = $this->createMock(LinkedPackage::class);
        $fileSystem = $this->createMock(FilesystemOperator::class);

        $fileSystem->method('fileExists')->willReturn(true);
        $fileSystem->method('read')->willReturn(serialize([$package]));

        $repository = new LinkedPackagesRepository(
            $fileSystem,
            $io
        );

        $this->assertCount(1, $repository->all());
        $this->assertEquals($package, $repository->all()[0]);
    }
}
