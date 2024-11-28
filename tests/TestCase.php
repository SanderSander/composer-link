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

namespace Tests;

use Composer\Util\Filesystem;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected string $tmpAbsoluteDir;

    protected string $tmpRelativeDir;

    protected Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $tmp = 'tests' . DIRECTORY_SEPARATOR . 'tmp';
        $this->filesystem = new Filesystem();
        $this->filesystem->emptyDirectory($tmp);

        $this->tmpAbsoluteDir = realpath($tmp) . DIRECTORY_SEPARATOR;
        $this->tmpRelativeDir = $tmp . DIRECTORY_SEPARATOR;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->filesystem->removeDirectory($this->tmpAbsoluteDir);
    }
}
