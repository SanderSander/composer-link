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
use ComposerLink\Commands\LinkedCommand;
use ComposerLink\Package\LinkedPackage;
use ComposerLink\Plugin;
use ComposerLink\Repository\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class LinkedCommandTest extends TestCase
{
    protected Application $application;

    /** @var Plugin&MockObject */
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
        $output->expects(static::once())
            ->method('writeln')
            ->with('No packages are linked');

        $input = new StringInput('linked');
        static::assertSame(0, $this->application->run($input, $output));
    }

    public function test_linked_packages(): void
    {
        $repository = $this->createMock(Repository::class);
        $repository->method('all')->willReturn([
            $this->getMockedLinkedPackage('test-1'),
            $this->getMockedLinkedPackage('test-2'),
        ]);
        $this->plugin->method('getRepository')->willReturn($repository);

        $output = $this->createMock(OutputInterface::class);
        $output->expects(static::exactly(2))
            ->method('writeln')
            ->with(static::logicalOr(
                static::equalTo("package/test-1\t../package/test-1"),
                static::equalTo("package/test-2\t../package/test-2")
            ));

        $input = new StringInput('linked');
        static::assertSame(0, $this->application->run($input, $output));
    }

    private function getMockedLinkedPackage(string $name): LinkedPackage
    {
        $package = $this->createMock(LinkedPackage::class);
        $package->method('getName')->willReturn('package/' . $name);
        $package->method('getPath')->willReturn('../package/' . $name);

        return $package;
    }
}
