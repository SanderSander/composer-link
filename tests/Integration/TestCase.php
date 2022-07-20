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

    /**
     * @return string[]
     */
    protected function runLinkCommand(string $command): array
    {
        $output = [];
        exec('composer ' . $command . ' 2>&1', $output);

        return $output;
    }

    protected function useComposerLinkLocal(): void
    {
        file_put_contents('composer.json', '{
            "repositories": [
                {
                    "type": "path",
                    "url": "' . $this->initialDirectory . '"
                }
            ],
            "config": {
                "allow-plugins": {
                    "sandersander/composer-link": true
                }
            }
        }');

        shell_exec('composer require sandersander/composer-link @dev &> /dev/null');
    }

    protected function useComposerLinkGlobal(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        chdir($this->initialDirectory);
    }
}
