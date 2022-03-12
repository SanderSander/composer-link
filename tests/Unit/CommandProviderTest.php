<?php declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Copyright (c) 2021-2022 Sander Visser <themastersleader@hotmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @link https://github.com/SanderSander/composer-link
 */

namespace Tests\Unit;

use Composer\IO\IOInterface;
use ComposerLink\CommandProvider;
use ComposerLink\Commands\LinkCommand;
use ComposerLink\Commands\LinkedCommand;
use ComposerLink\Commands\UnlinkCommand;
use ComposerLink\Plugin;
use PHPUnit\Framework\TestCase;

class CommandProviderTest extends TestCase
{
    public function test_command_provider(): void
    {
        $arguments['io'] = $this->createStub(IOInterface::class);
        $arguments['plugin'] = $this->createStub(Plugin::class);

        $provider = new CommandProvider($arguments);
        $commands = $provider->getCommands();

        $this->assertCount(3, $commands);
        $this->assertInstanceOf(LinkCommand::class, $commands[0]);
        $this->assertInstanceOf(UnlinkCommand::class, $commands[1]);
        $this->assertInstanceOf(LinkedCommand::class, $commands[2]);
    }
}
