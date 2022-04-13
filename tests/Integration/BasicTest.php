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
    public function test_package_can_be_linked(): void
    {
        $output = new BufferedOutput();
        $this->application->run(new StringInput('--version'), $output);
        static::assertStringContainsString('Composer version 2.3', $output->fetch());
    }
}
