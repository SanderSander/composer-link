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
use RuntimeException;
use Tests\TestCase as BaseCase;

abstract class TestCase extends BaseCase
{
    protected Application $application;

    private string $initialDirectory;

    public function setUp(): void
    {
        parent::setUp();

        if (getcwd() === false) {
            throw new RuntimeException('Unable to get CMD');
        }
        $this->initialDirectory = getcwd();

        chdir($this->tmpAbsoluteDir);
    }

    public function getMockDirectory(): string
    {
        return $this->initialDirectory . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock';
    }

    protected function runLinkCommand(string $command): string
    {
        $output = [];
        exec('composer ' . $command . ' 2>&1', $output);

        return implode(PHP_EOL, $output);
    }

    protected function useComposerLinkLocal(): void
    {
        file_put_contents('composer.json', '{
            "repositories": [
                {
                    "type": "path",
                    "url": "' . addslashes($this->initialDirectory) . '"
                }
            ],
            "config": {
                "allow-plugins": {
                    "sandersander/composer-link": true
                }
            }
        }');

        shell_exec('composer require sandersander/composer-link @dev  2>&1');
    }

    protected function useComposerLinkGlobal(): void
    {
        $global = exec('composer config --global home');
        file_put_contents($global . DIRECTORY_SEPARATOR . 'composer.json', '{
            "repositories": [
                {
                    "type": "path",
                    "url": "' . addslashes($this->initialDirectory) . '"
                }
            ],
            "config": {
                "allow-plugins": {
                    "sandersander/composer-link": true
                }
            }
        }');

        shell_exec('composer global require sandersander/composer-link @dev  2>&1');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        chdir($this->initialDirectory);
    }
}
