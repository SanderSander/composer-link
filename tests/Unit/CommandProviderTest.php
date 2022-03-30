<?php declare(strict_types=1);

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
