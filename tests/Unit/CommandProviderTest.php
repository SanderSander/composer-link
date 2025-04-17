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

use Composer\IO\IOInterface;
use ComposerLink\CommandProvider;
use ComposerLink\Commands\LinkCommand;
use ComposerLink\Commands\LinkedCommand;
use ComposerLink\Commands\UnlinkAllCommand;
use ComposerLink\Commands\UnlinkCommand;
use ComposerLink\Plugin;
use PHPUnit\Framework\TestCase;

class CommandProviderTest extends TestCase
{
    public function test_command_provider(): void
    {
        $arguments = [];
        $arguments['io'] = static::createStub(IOInterface::class);
        $arguments['plugin'] = static::createStub(Plugin::class);

        $provider = new CommandProvider($arguments);
        $commands = $provider->getCommands();

        static::assertCount(4, $commands);
        static::assertInstanceOf(LinkCommand::class, $commands[0]);
        static::assertInstanceOf(UnlinkCommand::class, $commands[1]);
        static::assertInstanceOf(LinkedCommand::class, $commands[2]);
        static::assertInstanceOf(UnlinkAllCommand::class, $commands[3]);
    }
}
