<?php

declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Copyright (c) 2021-2024 Sander Visser <themastersleader@hotmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 *
 * @link https://github.com/SanderSander/composer-link
 */

namespace Tests\Unit\Commands;

use Composer\Console\Application;
use ComposerLink\Commands\LinkCommand;
use ComposerLink\LinkedPackage;
use ComposerLink\LinkedPackageFactory;
use ComposerLink\LinkManager;
use ComposerLink\Plugin;
use ComposerLink\Repository\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\Unit\TestCase;

class LinkCommandTest extends TestCase
{
    /** @var Plugin&MockObject */
    protected Plugin $plugin;

    /** @var OutputInterface&MockObject */
    protected OutputInterface $output;

    /** @var LinkManager&MockObject */
    protected LinkManager $linkManager;

    /** @var Repository&MockObject */
    protected Repository $repository;

    /** @var LinkedPackageFactory&MockObject */
    protected LinkedPackageFactory $packageFactory;

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
        $this->packageFactory = $this->createMock(LinkedPackageFactory::class);
        $this->package = $this->createMock(LinkedPackage::class);

        $this->plugin->method('getRepository')->willReturn($this->repository);
        $this->plugin->method('getLinkManager')->willReturn($this->linkManager);
        $this->plugin->method('getPackageFactory')->willReturn($this->packageFactory);

        $command = new LinkCommand($this->plugin);
        $this->application = new Application();
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);
        $this->application->add($command);
    }

    public function test_link_command(): void
    {
        $this->packageFactory->expects(static::once())
            ->method('fromPath')
            ->with('/test-path');

        $this->repository->expects(static::once())->method('store');
        $this->repository->expects(static::once())->method('persist');
        $this->linkManager->expects(static::once())->method('linkPackage');

        $input = new StringInput('link /test-path');
        static::assertSame(0, $this->application->run($input, $this->output));
    }

    public function test_only_installed_when_not_installed(): void
    {
        $this->packageFactory->expects(static::once())
            ->method('fromPath')
            ->with('/test-path');

        $this->repository->expects(static::never())->method('store');
        $this->repository->expects(static::never())->method('persist');
        $this->linkManager->expects(static::never())->method('linkPackage');

        $input = new StringInput('link /test-path --only-installed');
        static::assertSame(0, $this->application->run($input, $this->output));
    }

    public function test_only_installed_when_installed(): void
    {
        $this->packageFactory->expects(static::once())
            ->method('fromPath')
            ->with('/test-path')
            ->willReturn($this->mockPackage());

        $this->repository->expects(static::once())->method('store');
        $this->repository->expects(static::once())->method('persist');
        $this->linkManager->expects(static::once())->method('linkPackage');

        $input = new StringInput('link /test-path --only-installed');
        static::assertSame(0, $this->application->run($input, $this->output));
    }

    public function test_link_command_from_global(): void
    {
        $this->plugin->method('isGlobal')->willReturn(true);
        $this->packageFactory->expects(static::once())
            ->method('fromPath')
            ->with(realpath(__DIR__ . '/../..'));

        $this->repository->expects(static::once())->method('store');
        $this->repository->expects(static::once())->method('persist');
        $this->linkManager->expects(static::once())->method('linkPackage');

        $input = new StringInput('link tests');
        static::assertSame(0, $this->application->run($input, $this->output));
    }

    public function test_existing_path(): void
    {
        $this->repository->expects(static::once())->method('findByPath')
            ->with('/test-path')
            ->willReturn($this->createMock(LinkedPackage::class));

        $this->output->expects(static::once())->method('writeln')
            ->with('<warning>Package in path "/test-path" already linked</warning>');

        $input = new StringInput('link /test-path');
        static::assertSame(0, $this->application->run($input, $this->output));
    }

    public function test_existing_package_name(): void
    {
        $this->package->method('getName')->willReturn('test/package');
        $this->package->method('getPath')->willReturn('/test-path');

        $this->repository->expects(static::once())
            ->method('findByName')
            ->willReturn($this->package);

        $this->packageFactory->expects(static::once())
            ->method('fromPath')
            ->with('/test-path')
            ->willReturn($this->package);

        $command = new LinkCommand($this->plugin);
        static::assertSame('link', $command->getName());

        $this->output->expects(static::once())->method('writeln')
            ->with('<warning>Package "test/package" in "/test-path" already linked from path "/test-path"</warning>');

        $input = new StringInput('link /test-path');
        static::assertSame(0, $this->application->run($input, $this->output));
    }
}
