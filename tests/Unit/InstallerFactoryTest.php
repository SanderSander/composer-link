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

use Composer\Composer;
use Composer\IO\IOInterface;
use ComposerLink\InstallerFactory;

class InstallerFactoryTest extends TestCase
{
    public function test_factory(): void
    {
        $io = $this->createMock(IOInterface::class);
        $composer = $this->createMock(Composer::class);
        $factory = new InstallerFactory($io, $composer);
        $factory->create();
        static::expectNotToPerformAssertions();
    }
}
