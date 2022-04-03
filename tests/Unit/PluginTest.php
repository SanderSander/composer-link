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

namespace Tests\Unit;

use Composer\Composer;
use Composer\Config;
use Composer\Downloader\DownloadManager;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\RepositoryManager;
use Composer\Script\ScriptEvents;
use Composer\Util\Loop;
use ComposerLink\CommandProvider;
use ComposerLink\Plugin;
use ComposerLink\Repository\Repository;

class PluginTest extends TestCase
{
    protected Repository $packagesRepository;

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function test_if_plugin_can_be_utilized(): void
    {
        $io = $this->createMock(IOInterface::class);
        $composer = $this->mockComposer();

        $plugin = new Plugin();
        $plugin->activate($composer, $io);

        $capabilities = $plugin->getCapabilities();
        $events = Plugin::getSubscribedEvents();

        static::assertArrayHasKey(ComposerCommandProvider::class, $capabilities);
        static::assertContains(CommandProvider::class, $capabilities);
        static::assertArrayHasKey(ScriptEvents::POST_UPDATE_CMD, $events);

        $plugin->deactivate($composer, $io);
        $plugin->uninstall($composer, $io);
    }

    private function mockComposer(): Composer
    {
        $downloader = $this->createMock(DownloadManager::class);
        $installationManager = $this->createMock(InstallationManager::class);
        $loop = $this->createMock(Loop::class);
        $config = $this->createMock(Config::class);
        $repositoryManager = $this->createMock(RepositoryManager::class);
        $localRepository = $this->createMock(InstalledRepositoryInterface::class);
        $repositoryManager->method('getLocalRepository')->willReturn($localRepository);

        $config->method('get')->with('vendor-dir')->willReturn($this->rootDir);

        $composer = $this->createMock(Composer::class);
        $composer->method('getDownloadManager')->willReturn($downloader);
        $composer->method('getInstallationManager')->willReturn($installationManager);
        $composer->method('getConfig')->willReturn($config);
        $composer->method('getLoop')->willReturn($loop);
        $composer->method('getRepositoryManager')->willReturn($repositoryManager);

        return $composer;
    }
}
