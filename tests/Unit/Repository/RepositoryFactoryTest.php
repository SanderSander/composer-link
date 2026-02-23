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

use Composer\Composer;
use ComposerLink\Package\LinkedPackageFactory;
use ComposerLink\Repository\RepositoryFactory;
use Tests\Unit\TestCase;

class RepositoryFactoryTest extends TestCase
{
    public function test_creates_repository(): void
    {
        $factory = new RepositoryFactory();
        $factory->create(
            $this->tmpAbsoluteDir . 'linked-packages.json',
            $this->createMock(LinkedPackageFactory::class),
            $this->createMock(Composer::class)
        );

        static::expectNotToPerformAssertions();
    }
}
