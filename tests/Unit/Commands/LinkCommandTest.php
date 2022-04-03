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

use ComposerLink\Commands\LinkCommand;
use ComposerLink\LinkedPackage;
use ComposerLink\LinkedPackageFactory;
use ComposerLink\LinkManager;
use ComposerLink\Plugin;
use ComposerLink\Repository\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

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

        $this->application = new Application();
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);
        $this->application->add(new LinkCommand($this->plugin));
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

    public function test_existing_path(): void
    {
        $this->repository->expects(static::once())->method('findByPath')
            ->with('/test-path')
            ->willReturn($this->createMock(LinkedPackage::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Package in path "/test-path" already linked');

        $input = new StringInput('link /test-path');
        static::assertSame(1, $this->application->run($input, $this->output));
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Package "test/package" already linked from path "/test-path"');

        $input = new StringInput('link /test-path');
        static::assertSame(1, $this->application->run($input, $this->output));
    }
}
