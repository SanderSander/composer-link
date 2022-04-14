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

namespace Tests\Unit\Commands;

use Composer\Console\Application;
use ComposerLink\Commands\UnlinkCommand;
use ComposerLink\LinkedPackage;
use ComposerLink\LinkManager;
use ComposerLink\Plugin;
use ComposerLink\Repository\Repository;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class UnlinkCommandTest extends TestCase
{
    /** @var Plugin&MockObject */
    protected Plugin $plugin;

    /** @var OutputInterface&MockObject */
    protected OutputInterface $output;

    /** @var LinkManager&MockObject */
    protected LinkManager $linkManager;

    /** @var Repository&MockObject */
    protected Repository $repository;

    /** @var LinkedPackage&MockObject */
    protected LinkedPackage $package;

    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = $this->createMock(Plugin::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->linkManager = $this->createMock(LinkManager::class);
        $this->repository = $this->createMock(Repository::class);
        $this->package = $this->createMock(LinkedPackage::class);

        $this->plugin->method('getRepository')->willReturn($this->repository);
        $this->plugin->method('getLinkManager')->willReturn($this->linkManager);

        $this->application = new Application();
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);
        $this->application->add(new UnlinkCommand($this->plugin));
    }

    public function test_link_command_for_existing_package(): void
    {
        $this->repository->expects(static::once())->method('findByPath')->willReturn($this->package);
        $this->repository->expects(static::once())->method('remove')->with($this->package);
        $this->repository->expects(static::once())->method('persist');
        $this->linkManager->expects(static::once())->method('unlinkPackage')->with($this->package);

        $input = new StringInput('unlink /test-path');
        static::assertSame(0, $this->application->run($input, $this->output));
    }

    public function test_link_command_for_existing_package_global(): void
    {
        $this->plugin->method('isGlobal')->willReturn(true);
        $this->repository->expects(static::once())->method('findByPath')->willReturn($this->package);
        $this->repository->expects(static::once())->method('remove')->with($this->package);
        $this->repository->expects(static::once())->method('persist');
        $this->linkManager->expects(static::once())->method('unlinkPackage')->with($this->package);

        $input = new StringInput('unlink tests');
        static::assertSame(0, $this->application->run($input, $this->output));
    }

    public function test_link_command_for_non_existing_package(): void
    {
        $this->repository->expects(static::once())->method('findByPath')->willReturn(null);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No linked package found in path "/test-path"');

        $this->application->run(new StringInput('unlink /test-path'), $this->output);
    }
}
