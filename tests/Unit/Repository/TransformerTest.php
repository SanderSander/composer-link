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

use Composer\Package\PackageInterface;
use ComposerLink\Repository\Transformer;
use Tests\Unit\TestCase;

class TransformerTest extends TestCase
{
    public function test_load(): void
    {
        $transformer = new Transformer();
        $package = $transformer->load(
            [
                'name' => 'test/package',
                'path' => '../path',
                'installationPath' => 'install-path/',
                'package' => [
                    'name' => 'test/package',
                    'version' => 'dev-master',
                ],
                'originalPackage' => [
                    'name' => 'test/package',
                    'version' => 'dev-master',
                ],
                'withoutDependencies' => true,
            ]
        );

        static::assertInstanceOf(PackageInterface::class, $package->getOriginalPackage());
        static::assertEquals('test/package', $package->getName());
        static::assertEquals('../path', $package->getPath());
        static::assertEquals('install-path/', $package->getInstallationPath());
        static::assertTrue($package->isWithoutDependencies());

        $package = $transformer->load(
            [
                'name' => 'test/package',
                'path' => '../path',
                'installationPath' => 'install-path/',
                'package' => [
                    'name' => 'test/package',
                    'version' => 'dev-master',
                ],
                'withoutDependencies' => false,
            ]
        );
        static::assertNull($package->getOriginalPackage());
        static::assertFalse($package->isWithoutDependencies());
    }

    public function test_export(): void
    {
        $transformer = new Transformer();

        $data = $transformer->export($this->mockPackage());
        static::assertEquals([
            'path' => '../test-path-package',
            'installationPath' => '../install-path-package',
            'package' => [
                'name' => '',
                'version' => '',
                'version_normalized' => '',
                'type' => '',
            ],
            'originalPackage' => [
                'name' => '',
                'version' => '',
                'version_normalized' => '',
                'type' => '',
            ],
            'withoutDependencies' => false,
        ], $data);

        $data = $transformer->export($this->mockPackage('package', false));
        static::assertEquals([
            'path' => '../test-path-package',
            'installationPath' => '../install-path-package',
            'package' => [
                'name' => '',
                'version' => '',
                'version_normalized' => '',
                'type' => '',
            ],
            'withoutDependencies' => false,
        ], $data);
    }
}
