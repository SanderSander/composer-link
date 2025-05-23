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

namespace Tests\Integration;

use Composer\Console\Application;
use RuntimeException;
use Tests\TestCase as BaseCase;

abstract class TestCase extends BaseCase
{
    public const RELATIVE_PATH_MOCK = '..' . DIRECTORY_SEPARATOR . 'composer-link' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock';

    protected Application $application;

    private string $thisPackagePath;

    protected string $composerGlobalDir;

    public function setUp(): void
    {
        parent::setUp();
        if (getcwd() === false) {
            throw new RuntimeException('Unable to get CMD');
        }
        $this->thisPackagePath = (string) getcwd();
        $this->composerGlobalDir = (string) realpath((string) exec('composer config --global home'));

        chdir($this->tmpAbsoluteDir);
    }

    public function getMockDirectory(): string
    {
        return $this->thisPackagePath . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'mock';
    }

    protected function runComposerCommand(string $command): string
    {
        $output = [];
        exec('composer ' . $command . ' 2>&1', $output);

        return implode(PHP_EOL, $output);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getCurrentComposeFile(): array
    {
        /** @var string $content */
        $content = file_get_contents('composer.json');

        return json_decode($content, true);
    }

    /**
     * @param array<string, mixed> $composeFile
     */
    protected function setCurrentComposeFile(array $composeFile): void
    {
        file_put_contents('composer.json', json_encode($composeFile, JSON_PRETTY_PRINT));
    }

    public function getThisPackagePath(): string
    {
        return $this->thisPackagePath;
    }

    /**
     * Loads an older version with upgrade protection.
     */
    protected function useComposerLinkLocalOld(): void
    {
        file_put_contents('composer.json', '{
            "config": {
                "allow-plugins": {
                    "sandersander/composer-link": true
                }
            }
        }');

        shell_exec('composer require sandersander/composer-link 0.4.1  2>&1');
    }

    protected function useComposerLinkLocal(): void
    {
        file_put_contents('composer.json', '{
            "repositories": [
                {
                    "type": "path",
                    "url": "' . addslashes($this->thisPackagePath) . '"
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
        file_put_contents($this->composerGlobalDir . DIRECTORY_SEPARATOR . 'composer.json', '{
            "repositories": [
                {
                    "type": "path",
                    "url": "' . addslashes($this->thisPackagePath) . '"
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
        // We have to change directory, before parent class remove the directory.
        // Windows has problems with removing directories when they are open in console
        chdir($this->thisPackagePath);
        parent::tearDown();
    }
}
