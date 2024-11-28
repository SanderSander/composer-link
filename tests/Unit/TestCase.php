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

use Composer\Package\PackageInterface;
use ComposerLink\LinkedPackage;
use Tests\TestCase as BaseTest;

abstract class TestCase extends BaseTest
{
    /** @SuppressWarnings(PHPMD.BooleanArgumentFlag) */
    protected function mockPackage(string $name = 'package', bool $withOriginalPackage = true): LinkedPackage
    {
        $package = $this->createMock(LinkedPackage::class);
        $package->method('getName')->willReturn('test/' . $name);
        $package->method('getPath')->willReturn('../test-path-' . $name);
        $package->method('getInstallationPath')->willReturn('../install-path-' . $name);
        if ($withOriginalPackage) {
            $package->method('getOriginalPackage')
                ->willReturn($this->createMock(PackageInterface::class));
        }

        return $package;
    }
}
