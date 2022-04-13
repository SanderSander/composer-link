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

namespace Tests\Integration;

use Composer\Console\Application;
use Composer\Util\Filesystem;
use PHPUnit\Framework\TestCase as BaseCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\ApplicationTester;

abstract class TestCase extends BaseCase
{
    protected Application $application;

    protected ApplicationTester $tester;

    private string $workingDirectory;

    public function setUp(): void
    {
        parent::setUp();
        $this->workingDirectory = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'temp';

        $filesystem = new Filesystem();
        if (is_dir($this->workingDirectory)) {
            $filesystem->removeDirectory($this->workingDirectory);
        }

        mkdir($this->workingDirectory);
        chdir($this->workingDirectory);
        $this->application = new Application();
        $this->application->setAutoExit(false);
        $this->application->setCatchExceptions(false);
        $this->tester = new ApplicationTester($this->application);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $filesystem = new Filesystem();
        if (is_dir($this->workingDirectory)) {
            $filesystem->removeDirectory($this->workingDirectory);
        }
    }
}
