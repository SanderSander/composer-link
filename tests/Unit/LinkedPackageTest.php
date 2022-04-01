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

namespace Tests\Unit;

use Composer\Package\CompletePackage;
use Composer\Package\PackageInterface;
use ComposerLink\LinkedPackage;
use PHPUnit\Framework\TestCase;

class LinkedPackageTest extends TestCase
{
    public function test_linked_package(): void
    {
        $package = $this->createStub(CompletePackage::class);
        $package->method('getName')->willReturn('test/package');
        $originalPackage = $this->createStub(PackageInterface::class);

        $linkedPackage = new LinkedPackage(
            '/test-path',
            $package,
            $originalPackage,
            '/test-install-path'
        );

        $this->assertSame('/test-install-path', $linkedPackage->getInstallationPath());
        $this->assertSame('/test-path', $linkedPackage->getPath());
        $this->assertSame($package, $linkedPackage->getPackage());
        $this->assertSame($originalPackage, $linkedPackage->getOriginalPackage());
        $this->assertSame('test/package', $linkedPackage->getName());
        $newOriginalPackage = $this->createMock(PackageInterface::class);
        $linkedPackage->setOriginalPackage($newOriginalPackage);
        $this->assertSame($newOriginalPackage, $linkedPackage->getOriginalPackage());
    }
}
