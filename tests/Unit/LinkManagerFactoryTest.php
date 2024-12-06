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
use ComposerLink\LinkManagerFactory;
use ComposerLink\Repository\Repository;

class LinkManagerFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $factory = new LinkManagerFactory();
        $factory->create(
            $this->createMock(Repository::class),
            $this->createMock(InstallerFactory::class),
            $this->createMock(IOInterface::class),
            $this->createMock(Composer::class),
        );

        self::expectNotToPerformAssertions();
    }
}
