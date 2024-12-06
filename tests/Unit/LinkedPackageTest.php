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

use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;
use ComposerLink\Package\LinkedPackage;
use PHPUnit\Framework\TestCase;

class LinkedPackageTest extends TestCase
{
    public function test_linked_package(): void
    {
        $package = self::createStub(CompletePackageInterface::class);
        $package->method('getName')->willReturn('test/package');
        $originalPackage = self::createStub(PackageInterface::class);

        $linkedPackage = new LinkedPackage(
            $package,
            '/test-path',
            '/test-install-path',
            $originalPackage,
        );

        static::assertSame('/test-install-path', $linkedPackage->getInstallationPath());
        static::assertSame('/test-path', $linkedPackage->getPath());
        static::assertSame($package, $linkedPackage->getLinkedPackage());
        static::assertSame($originalPackage, $linkedPackage->getOriginalPackage());
        static::assertSame('test/package', $linkedPackage->getName());

        $newOriginalPackage = $this->createMock(PackageInterface::class);
        $linkedPackage->setOriginalPackage($newOriginalPackage);
        static::assertSame($newOriginalPackage, $linkedPackage->getOriginalPackage());
    }
}
