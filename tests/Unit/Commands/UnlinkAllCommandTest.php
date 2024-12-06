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

namespace Tests\Unit\Commands;

use Composer\Console\Application;
use ComposerLink\Commands\UnlinkAllCommand;
use ComposerLink\LinkManager;
use ComposerLink\Package\LinkedPackage;
use ComposerLink\Plugin;
use ComposerLink\Repository\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class UnlinkAllCommandTest extends TestCase
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
        $this->application->add(new UnlinkAllCommand($this->plugin));
    }

    public function test_unlink_all_command(): void
    {
        $this->repository->expects(static::once())->method('all')->willReturn([$this->package, $this->package]);
        $this->linkManager->expects(static::exactly(2))->method('remove')->with($this->package);

        $input = new StringInput('unlink-all');
        static::assertSame(0, $this->application->run($input, $this->output));
    }
}
