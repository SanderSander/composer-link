<?php

declare(strict_types=1);

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

namespace Tests\Integration;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class BasicTest extends TestCase
{
    public function test_package_can_be_linked_and_unlinked(): void
    {
        $output = new BufferedOutput();
        $this->application->run(new StringInput('linked --no-interaction'), $output);
        static::assertStringContainsString('No packages are linked', $output->fetch());

        $this->application->run(new StringInput('link ../mock/package-1 --no-interaction'), $output);
        static::assertStringContainsString('Installing test/package-1 (dev-master): Symlinking from ../mock/package-1', $output->fetch());

        $this->application->run(new StringInput('linked --no-interaction'), $output);
        static::assertStringContainsString('test/package-1	../mock/package-1', $output->fetch());

        $this->application->run(new StringInput('unlink ../mock/package-1 --no-interaction'), $output);
        static::assertStringContainsString('Removing test/package-1 (dev-master)', $output->fetch());

        $this->application->run(new StringInput('linked --no-interaction'), $output);
        static::assertStringContainsString('No packages are linked', $output->fetch());
    }
}
