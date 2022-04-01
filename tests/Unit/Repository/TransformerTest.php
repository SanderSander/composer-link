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

use Composer\Package\PackageInterface;
use ComposerLink\LinkedPackage;
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
                    'version' => 'dev-master'
                ],
                'originalPackage' => [
                    'name' => 'test/package',
                    'version' => 'dev-master'
                ]
            ]
        );

        $this->assertInstanceOf(LinkedPackage::class, $package);
        $this->assertInstanceOf(PackageInterface::class, $package->getPackage());
        $this->assertInstanceOf(PackageInterface::class, $package->getOriginalPackage());
        $this->assertEquals('test/package', $package->getName());
        $this->assertEquals('../path', $package->getPath());
        $this->assertEquals('install-path/', $package->getInstallationPath());

        $package = $transformer->load(
            [
                'name' => 'test/package',
                'path' => '../path',
                'installationPath' => 'install-path/',
                'package' => [
                    'name' => 'test/package',
                    'version' => 'dev-master'
                ]
            ]
        );
        $this->assertNull($package->getOriginalPackage());
    }

    public function test_export(): void
    {
        $transformer = new Transformer();

        $data = $transformer->export($this->mockPackage());
        $this->assertEquals([
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
        ], $data);

        $data = $transformer->export($this->mockPackage('package', false));
        $this->assertEquals([
            'path' => '../test-path-package',
            'installationPath' => '../install-path-package',
            'package' => [
                'name' => '',
                'version' => '',
                'version_normalized' => '',
                'type' => '',
            ]
        ], $data);
    }
}
