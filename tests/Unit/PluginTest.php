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
use Composer\Util\Filesystem;
use Composer\Util\Loop;
use ComposerLink\Actions\LinkPackages;
use ComposerLink\CommandProvider;
use ComposerLink\Plugin;
use ComposerLink\Repository\Repository;
use RuntimeException;

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
        static::assertFalse($plugin->isGlobal());

        $plugin->getPackageFactory();
        $plugin->getLinkManager();
        $plugin->getRepository();
        $plugin->deactivate($composer, $io);
        $plugin->uninstall($composer, $io);
    }

    public function test_plugin_throws_exception_package_factory(): void
    {
        self::expectException(RuntimeException::class);
        $plugin = new Plugin();
        $plugin->getPackageFactory();
    }

    public function test_plugin_throws_exception_link_manager(): void
    {
        self::expectException(RuntimeException::class);
        $plugin = new Plugin();
        $plugin->getLinkManager();
    }

    public function test_plugin_throws_exception_repository(): void
    {
        self::expectException(RuntimeException::class);
        $plugin = new Plugin();
        $plugin->getRepository();
    }

    public function test_plugin_link_linked_packages(): void
    {
        $linkPackages = $this->createMock(LinkPackages::class);
        $linkPackages->expects(static::once())->method('execute');
        $plugin = new Plugin($this->createMock(Filesystem::class), $linkPackages);
        $plugin->linkLinkedPackages();

        static::expectException(RuntimeException::class);
        $plugin = new Plugin($this->createMock(Filesystem::class));
        $plugin->linkLinkedPackages();
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

        $config->method('get')
            ->withConsecutive(['vendor-dir'], ['home'])
            ->willReturnOnConsecutiveCalls($this->rootDir, $this->rootDir);

        $composer = $this->createMock(Composer::class);
        $composer->method('getDownloadManager')->willReturn($downloader);
        $composer->method('getInstallationManager')->willReturn($installationManager);
        $composer->method('getConfig')->willReturn($config);
        $composer->method('getLoop')->willReturn($loop);
        $composer->method('getRepositoryManager')->willReturn($repositoryManager);

        return $composer;
    }
}
