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

namespace Tests\Unit\Commands;

use ComposerLink\Commands\LinkCommand;
use ComposerLink\LinkedPackage;
use ComposerLink\LinkedPackageFactory;
use ComposerLink\LinkedPackagesRepository;
use ComposerLink\LinkManager;
use ComposerLink\Plugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class LinkCommandTest extends TestCase
{
    /** @var Plugin&MockObject  */
    protected Plugin $plugin;

    /** @var OutputInterface&MockObject  */
    protected OutputInterface $output;

    /** @var LinkManager&MockObject  */
    protected LinkManager $linkManager;

    /** @var LinkedPackagesRepository&MockObject  */
    protected LinkedPackagesRepository $repository;

    /** @var LinkedPackageFactory&MockObject  */
    protected LinkedPackageFactory $packageFactory;

    /** @var LinkedPackage&MockObject  */
    protected LinkedPackage $package;

    /** @var Application */
    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = $this->createMock(Plugin::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->linkManager = $this->createMock(LinkManager::class);
        $this->repository = $this->createMock(LinkedPackagesRepository::class);
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
        $this->packageFactory->expects($this->once())
            ->method('fromPath')
            ->with('/test-path');

        $this->repository->expects($this->once())->method('store');
        $this->repository->expects($this->once())->method('persist');
        $this->linkManager->expects($this->once())->method('linkPackage');

        $input = new StringInput('link /test-path');
        $this->assertSame(0, $this->application->run($input, $this->output));
    }

    public function test_existing_path(): void
    {
        $this->repository->expects($this->once())->method('findByPath')
            ->with('/test-path')
            ->willReturn($this->createMock(LinkedPackage::class));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Package in path "/test-path" already linked');

        $input = new StringInput('link /test-path');
        $this->assertSame(1, $this->application->run($input, $this->output));
    }

    public function test_existing_package_name(): void
    {
        $this->package->method('getName')->willReturn('test/package');
        $this->package->method('getPath')->willReturn('/test-path');

        $this->repository->expects($this->once())
            ->method('findByName')
            ->willReturn($this->package);

        $this->packageFactory->expects($this->once())
            ->method('fromPath')
            ->with('/test-path')
            ->willReturn($this->package);

        $command = new LinkCommand($this->plugin);
        $this->assertSame('link', $command->getName());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Package "test/package" already linked from path "/test-path"');

        $input = new StringInput('link /test-path');
        $this->assertSame(1, $this->application->run($input, $this->output));
    }
}
