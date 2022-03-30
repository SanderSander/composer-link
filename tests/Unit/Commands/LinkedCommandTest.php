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

namespace Tests\Unit\Commands;

use Composer\Package\CompletePackage;
use ComposerLink\Commands\LinkedCommand;
use ComposerLink\LinkedPackage;
use ComposerLink\LinkedPackagesRepository;
use ComposerLink\Plugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class LinkedCommandTest extends TestCase
{
    protected Application $application;

    /** @var Plugin&MockObject  */
    protected Plugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plugin = $this->createMock(Plugin::class);

        $this->application = new Application();
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);
        $this->application->add(new LinkedCommand($this->plugin));
    }

    public function test_no_linked_packages(): void
    {
        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->once())
            ->method('writeln')
            ->with('No packages are linked');

        $input = new StringInput('linked');
        $this->assertSame(0, $this->application->run($input, $output));
    }

    public function test_linked_packages(): void
    {
        $repository = $this->createMock(LinkedPackagesRepository::class);
        $repository->method('all')->willReturn([
            $this->getMockedLinkedPackage('test-1'),
            $this->getMockedLinkedPackage('test-2'),
        ]);
        $this->plugin->method('getRepository')->willReturn($repository);

        $output = $this->createMock(OutputInterface::class);
        $output->expects($this->exactly(2))
            ->method('writeln')
            ->with($this->logicalOr(
                $this->equalTo('../package/test-1	package/test-1'),
                $this->equalTo('../package/test-2	package/test-2')
            ));

        $input = new StringInput('linked');
        $this->assertSame(0, $this->application->run($input, $output));
    }

    private function getMockedLinkedPackage(string $name): LinkedPackage
    {
        $completePackage = $this->createMock(CompletePackage::class);
        $completePackage->method('getName')->willReturn('package/' . $name);
        $package = $this->createMock(LinkedPackage::class);
        $package->method('getPath')->willReturn('../package/' . $name);
        $package->method('getPackage')->willReturn($completePackage);

        return $package;
    }
}
