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
use PHPUnit\Runner\BaseTestRunner;
use RuntimeException;
use Tests\TestCase as BaseCase;

abstract class TestCase extends BaseCase
{
    protected Application $application;

    private string $initialDirectory;

    /**
     * @var string[];
     */
    private array $output = [];

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

    /**
     * @return string[]
     */
    protected function runLinkCommand(string $command): array
    {
        $output = [];
        exec('composer ' . $command . ' 2>&1', $output);
        $this->output = array_merge($this->output, $output);

        return $output;
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
        $status = $this->getStatus();
        //if ($status == BaseTestRunner::STATUS_ERROR || $status == BaseTestRunner::STATUS_FAILURE) {
            echo str_repeat(PHP_EOL, 2) .
                implode(PHP_EOL, $this->output) .
                str_repeat(PHP_EOL, 2);
            var_dump($this->getStatus());
        //}
        $this->output = [];
    }
}
