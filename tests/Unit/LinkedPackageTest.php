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

        static::assertSame('/test-install-path', $linkedPackage->getInstallationPath());
        static::assertSame('/test-path', $linkedPackage->getPath());
        static::assertSame($package, $linkedPackage->getPackage());
        static::assertSame($originalPackage, $linkedPackage->getOriginalPackage());
        static::assertSame('test/package', $linkedPackage->getName());
        $newOriginalPackage = $this->createMock(PackageInterface::class);
        $linkedPackage->setOriginalPackage($newOriginalPackage);
        static::assertSame($newOriginalPackage, $linkedPackage->getOriginalPackage());
    }
}
