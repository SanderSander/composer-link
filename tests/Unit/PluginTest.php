<?php declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Copyright (c) 2021-2022 Sander Visser <themastersleader@hotmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
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
use ComposerLink\LinkedPackageFactory;
use ComposerLink\LinkedPackagesRepository;
use ComposerLink\LinkManager;
use ComposerLink\Plugin;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    protected LinkedPackagesRepository $packagesRepository;

    public function test_if_plugin_can_be_utilized(): void
    {
        $io =$this->createMock(IOInterface::class);
        $composer = $this->mockComposer();

        $plugin = new Plugin();
        $plugin->activate($composer, $io);

        $capabilities = $plugin->getCapabilities();
        $events = Plugin::getSubscribedEvents();

        $this->assertArrayHasKey(ComposerCommandProvider::class, $capabilities);
        $this->assertContains(CommandProvider::class, $capabilities);
        $this->assertInstanceOf(LinkedPackagesRepository::class, $plugin->getRepository());
        $this->assertInstanceOf(LinkManager::class, $plugin->getLinkManager());
        $this->assertInstanceOf(LinkedPackageFactory::class, $plugin->getPackageFactory());
        $this->assertArrayHasKey(ScriptEvents::POST_UPDATE_CMD, $events);

        $plugin->deactivate($composer, $io);
        $plugin->uninstall($composer, $io);
    }


    public function test_linking_linked_packages(): void
    {
        $io =$this->createMock(IOInterface::class);
        $composer = $this->mockComposer();

        $plugin = new Plugin();
        $plugin->activate($composer, $io);

        $capabilities = $plugin->getCapabilities();
        $events = Plugin::getSubscribedEvents();

        $this->assertArrayHasKey(ComposerCommandProvider::class, $capabilities);
        $this->assertContains(CommandProvider::class, $capabilities);
        $this->assertInstanceOf(LinkedPackagesRepository::class, $plugin->getRepository());
        $this->assertInstanceOf(LinkManager::class, $plugin->getLinkManager());
        $this->assertInstanceOf(LinkedPackageFactory::class, $plugin->getPackageFactory());
        $this->assertArrayHasKey(ScriptEvents::POST_UPDATE_CMD, $events);

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

        $config->method('get')->with('vendor-dir')->willReturn('./test-vendor');

        $composer = $this->createMock(Composer::class);
        $composer->method('getDownloadManager')->willReturn($downloader);
        $composer->method('getInstallationManager')->willReturn($installationManager);
        $composer->method('getConfig')->willReturn($config);
        $composer->method('getLoop')->willReturn($loop);
        $composer->method('getRepositoryManager')->willReturn($repositoryManager);

        return $composer;
    }
}
