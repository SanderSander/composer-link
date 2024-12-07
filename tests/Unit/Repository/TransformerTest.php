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

use ComposerLink\Package\LinkedPackageFactory;
use ComposerLink\Repository\Transformer;
use Tests\Unit\TestCase;

class TransformerTest extends TestCase
{
    public function test_load(): void
    {
        $package = $this->mockPackage();
        $package->expects(static::once())->method('setWithoutDependencies')->with(true);
        $packageFactory = $this->createMock(LinkedPackageFactory::class);
        $packageFactory->expects(static::once())
            ->method('fromPath')
            ->with('../path')
            ->willReturn($package);
        $transformer = new Transformer($packageFactory);
        $transformer->load(
            [
                'path' => '../path',
            ]
        );
    }

    public function test_load_without_dependencies(): void
    {
        $package = $this->mockPackage('package', false);
        $package->expects(static::once())->method('setWithoutDependencies')->with(false);

        $packageFactory = $this->createMock(LinkedPackageFactory::class);
        $packageFactory->expects(static::once())
            ->method('fromPath')
            ->with('../path')
            ->willReturn($package);
        $transformer = new Transformer($packageFactory);
        $transformer->load(
            [
                'path' => '../path',
                'withoutDependencies' => false,
            ]
        );
    }

    public function test_export(): void
    {
        $packageFactory = $this->createMock(LinkedPackageFactory::class);
        $transformer = new Transformer($packageFactory);

        $data = $transformer->export($this->mockPackage());
        static::assertEquals([
            'path' => '../test-path-package',
            'withoutDependencies' => false,
        ], $data);

        $package = $this->mockPackage();
        $package->method('isWithoutDependencies')->willReturn(true);
        $data = $transformer->export($package);
        static::assertEquals([
            'path' => '../test-path-package',
            'withoutDependencies' => true,
        ], $data);
    }
}
