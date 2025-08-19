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

namespace Tests\Unit\Package;

use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Semver\Constraint\Constraint;
use ComposerLink\Package\LinkedPackage;
use PHPUnit\Framework\TestCase;

class LinkedPackageAliasTest extends TestCase
{
    public function test_creates_link_with_dev_linked_when_no_locked_package(): void
    {
        $package = static::createStub(CompletePackageInterface::class);
        $package->method('getName')->willReturn('test/package');
        $originalPackage = static::createStub(PackageInterface::class);

        $linkedPackage = new LinkedPackage(
            $package,
            '/test-path',
            '/test-install-path',
            $originalPackage,
            null // no locked package
        );

        $rootPackage = static::createStub(RootPackageInterface::class);
        $rootPackage->method('getName')->willReturn('root/package');

        $link = $linkedPackage->createLink($rootPackage);

        static::assertSame('root/package', $link->getSource());
        static::assertSame('test/package', $link->getTarget());
        static::assertInstanceOf(Constraint::class, $link->getConstraint());
        static::assertSame('== dev-linked', $link->getConstraint()->getPrettyString());
    }

    public function test_creates_link_with_locked_version_when_locked_package_exists(): void
    {
        $package = static::createStub(CompletePackageInterface::class);
        $package->method('getName')->willReturn('test/package');
        $originalPackage = static::createStub(PackageInterface::class);

        $lockedPackage = static::createStub(PackageInterface::class);
        $lockedPackage->method('getVersion')->willReturn('1.2.3');
        $lockedPackage->method('getPrettyVersion')->willReturn('1.2.3');

        $linkedPackage = new LinkedPackage(
            $package,
            '/test-path',
            '/test-install-path',
            $originalPackage,
            $lockedPackage
        );

        $rootPackage = static::createStub(RootPackageInterface::class);
        $rootPackage->method('getName')->willReturn('root/package');

        $link = $linkedPackage->createLink($rootPackage);

        static::assertSame('root/package', $link->getSource());
        static::assertSame('test/package', $link->getTarget());
        static::assertInstanceOf(Constraint::class, $link->getConstraint());
        // Constraint should match the locked version
        static::assertSame('== 1.2.3', $link->getConstraint()->getPrettyString());
    }

    public function test_sets_and_gets_locked_package(): void
    {
        $package = static::createStub(CompletePackageInterface::class);
        $package->method('getName')->willReturn('test/package');
        $originalPackage = static::createStub(PackageInterface::class);

        $linkedPackage = new LinkedPackage(
            $package,
            '/test-path',
            '/test-install-path',
            $originalPackage
        );

        static::assertNull($linkedPackage->getLockedPackage());

        $lockedPackage = static::createStub(PackageInterface::class);
        $linkedPackage->setLockedPackage($lockedPackage);

        static::assertSame($lockedPackage, $linkedPackage->getLockedPackage());
    }
}
