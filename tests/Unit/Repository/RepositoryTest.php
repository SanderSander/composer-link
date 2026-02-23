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

namespace Tests\Unit\Repository;

use ComposerLink\Repository\Repository;
use ComposerLink\Repository\StorageInterface;
use ComposerLink\Repository\Transformer;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Tests\Unit\TestCase;

class RepositoryTest extends TestCase
{
    /** @var StorageInterface&MockObject */
    protected StorageInterface $storage;

    /** @var Transformer&MockObject */
    protected Transformer $transformer;

    public function setUp(): void
    {
        parent::setUp();

        $this->storage = $this->createMock(StorageInterface::class);
        $this->transformer = $this->createMock(Transformer::class);
    }

    protected function getRepository(): Repository
    {
        return new Repository(
            $this->storage,
            $this->transformer,
            []
        );
    }

    public function test_if_package_is_stored_and_persisted(): void
    {
        $package = $this->mockPackage();
        $repository = $this->getRepository();

        $repository->store($package);
        static::assertCount(1, $repository->all());
        static::assertEquals($package, $repository->all()[0]);
        static::assertNotSame($package, $repository->findByName('test/package'));
        $this->transformer->method('export')->willReturn(['path' => 'exists']);

        $this->storage->expects(static::once())
            ->method('write')
            ->with(static::callback(
                function (array $data) {
                    /** @var list<array{path: non-empty-string, withoutDependencies?: bool}> $packages */
                    $packages = $data['packages'];

                    self::assertCount(1, $packages);
                    self::assertSame(['path' => 'exists'], $packages[0]);

                    return true;
                }
            ));

        $repository->persist();
    }

    public function test_if_package_is_updated_when_stored(): void
    {
        $package1 = $this->mockPackage();
        $package2 = $this->mockPackage();
        $repository = $this->getRepository();

        $repository->store($package1);
        $repository->store($package2);

        static::assertCount(1, $repository->all());
        static::assertEquals($package2, $repository->findByName('test/package'));
    }

    public function test_find_by_path(): void
    {
        $package = $this->mockPackage();
        $repository = $this->getRepository();

        $repository->store($package);
        static::assertEquals($package, $repository->findByPath('../test-path-package'));
        static::assertNotSame($package, $repository->findByName('test/package'));
        static::assertNull($repository->findByPath('/test-path-other'));
    }

    public function test_find_by_name(): void
    {
        $package = $this->mockPackage();
        $repository = $this->getRepository();

        $repository->store($package);
        static::assertEquals($package, $repository->findByName('test/package'));
        static::assertNotSame($package, $repository->findByName('test/package'));
        static::assertNull($repository->findByName('test/package-other'));
    }

    public function test_package_is_removed(): void
    {
        $package = $this->mockPackage();
        $repository = $this->getRepository();

        $repository->store($package);
        $repository->remove($package);

        static::assertCount(0, $repository->all());

        $this->storage->expects(static::once())
            ->method('write')
            ->with(['packages' => [], 'unlinkedExtra' => []]);
        $repository->persist();
    }

    public function test_remove_throws_exception(): void
    {
        $package = $this->mockPackage();
        $repository = $this->getRepository();
        $this->expectException(RuntimeException::class);
        $repository->remove($package);
    }

    public function test_if_data_can_be_loaded_from_file(): void
    {
        $package = $this->mockPackage();
        $this->storage->method('hasData')->willReturn(true);
        $this->storage->method('read')
            ->willReturn(['packages' => [[]]]);
        $repository = $this->getRepository();

        $this->transformer->method('load')->willReturn($package);

        static::assertCount(1, $repository->all());
    }
}
