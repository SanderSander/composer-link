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

namespace Tests\Unit\Repository;

use Composer\IO\IOInterface;
use ComposerLink\LinkedPackage;
use ComposerLink\Repository\Repository;
use ComposerLink\Repository\Transformer;
use League\Flysystem\FilesystemOperator;
use Tests\Unit\TestCase;

class RepositoryTest extends TestCase
{
    /** @var IOInterface&\PHPUnit\Framework\MockObject\Stub */
    protected IOInterface $io;

    /** @var FilesystemOperator&\PHPUnit\Framework\MockObject\MockObject */
    protected FilesystemOperator $fileSystem;

    /** @var Transformer&\PHPUnit\Framework\MockObject\MockObject */
    protected Transformer $transformer;

    public function setUp(): void
    {
        parent::setUp();

        $this->io = $this->createStub(IOInterface::class);
        $this->fileSystem = $this->createMock(FilesystemOperator::class);
        $this->transformer = $this->createMock(Transformer::class);
    }

    protected function getRepository(): Repository
    {
        return new Repository(
            $this->fileSystem,
            $this->io,
            $this->transformer
        );
    }

    public function test_if_package_is_stored_and_persisted(): void
    {
        $package = $this->mockPackage();
        $repository = $this->getRepository();

        $repository->store($package);
        $this->assertCount(1, $repository->all());
        $this->assertEquals($package, $repository->all()[0]);
        $this->assertNotSame($package, $repository->findByName('test/package'));
        $this->transformer->method('export')->willReturn(['test' => 'exists']);

        $this->fileSystem->expects($this->once())
            ->method('write')
            ->with('linked-packages.json', $this->callback(function (string $json) {
                $data = json_decode($json, true);
                $this->assertCount(1, $data['packages']);
                $this->assertSame(['test' => 'exists'], $data['packages'][0]);
                return true;
            }));

        $repository->persist();
    }

    public function test_if_package_is_updated_when_stored(): void
    {
        $package1 = $this->mockPackage();
        $package2 = $this->mockPackage();
        $repository = $this->getRepository();

        $repository->store($package1);
        $repository->store($package2);

        $this->assertCount(1, $repository->all());
        $this->assertEquals($package2, $repository->findByName('test/package'));
    }

    public function test_find_by_path(): void
    {
        $package = $this->mockPackage();
        $repository = $this->getRepository();

        $repository->store($package);
        $this->assertEquals($package, $repository->findByPath('../test-path-package'));
        $this->assertNotSame($package, $repository->findByName('test/package'));
        $this->assertNull($repository->findByPath('/test-path-other'));
    }

    public function test_find_by_name(): void
    {
        $package = $this->mockPackage();
        $repository = $this->getRepository();

        $repository->store($package);
        $this->assertEquals($package, $repository->findByName('test/package'));
        $this->assertNotSame($package, $repository->findByName('test/package'));
        $this->assertNull($repository->findByName('test/package-other'));
    }

    public function test_package_is_removed(): void
    {
        $package = $this->mockPackage();
        $repository = $this->getRepository();

        $repository->store($package);
        $repository->remove($package);

        $this->assertCount(0, $repository->all());

        $this->fileSystem->expects($this->once())
            ->method('write')
            ->with('linked-packages.json', json_encode(['packages' => []]));
        $repository->persist();
    }

    public function test_remove_throws_exception(): void
    {
        $package = $this->mockPackage();
        $repository = $this->getRepository();
        $this->expectException(\RuntimeException::class);
        $repository->remove($package);
    }

    public function test_if_data_can_be_loaded_from_file(): void
    {
        $package = $this->mockPackage();
        $this->fileSystem->method('fileExists')->willReturn(true);
        $this->fileSystem->method('read')
            ->willReturn(json_encode(['packages' => [[]]]));
        $repository = $this->getRepository();

        $this->transformer->method('load')->willReturn($package);

        $this->assertCount(1, $repository->all());
        $resolved = $repository->all()[0];
        $this->assertInstanceOf(LinkedPackage::class, $resolved);
    }
}