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
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

/**
 *  @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PluginTest extends TestCase
{
    /**
     * @var Config&MockObject
     */
    protected Config $config;

    /**
     * @var Composer&MockObject
     */
    protected Composer $composer;

    /**
     * @var IOInterface&MockObject
     */
    protected IOInterface $io;

    protected function setUp(): void
    {
        parent::setUp();

        $this->io = $this->createMock(IOInterface::class);

        $downloader = $this->createMock(DownloadManager::class);
        $installationManager = $this->createMock(InstallationManager::class);
        $loop = $this->createMock(Loop::class);
        $this->config = $this->createMock(Config::class);
        $repositoryManager = $this->createMock(RepositoryManager::class);
        $localRepository = $this->createMock(InstalledRepositoryInterface::class);
        $repositoryManager->method('getLocalRepository')->willReturn($localRepository);

        $this->composer = $this->createMock(Composer::class);
        $this->composer->method('getDownloadManager')->willReturn($downloader);
        $this->composer->method('getInstallationManager')->willReturn($installationManager);
        $this->composer->method('getConfig')->willReturn($this->config);
        $this->composer->method('getLoop')->willReturn($loop);
        $this->composer->method('getRepositoryManager')->willReturn($repositoryManager);
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function test_if_plugin_can_be_utilized(): void
    {
        $this->config->method('get')
            ->willReturnCallback(function ($path) {
                return match ($path) {
                    'vendor-dir', 'home' => $this->tmpAbsoluteDir,
                    default => null,
                };
            });

        $plugin = new Plugin();
        $plugin->activate($this->composer, $this->io);

        $capabilities = $plugin->getCapabilities();
        $events = Plugin::getSubscribedEvents();

        static::assertArrayHasKey(ComposerCommandProvider::class, $capabilities);
        static::assertContains(CommandProvider::class, $capabilities);
        static::assertArrayHasKey(ScriptEvents::POST_UPDATE_CMD, $events);
        static::assertFalse($plugin->isGlobal());

        $plugin->getPackageFactory();
        $plugin->getLinkManager();
        $plugin->getRepository();
        $plugin->deactivate($this->composer, $this->io);
        $plugin->uninstall($this->composer, $this->io);
    }

    public function test_is_global(): void
    {
        $this->config->method('get')
            ->willReturnCallback(function ($path) {
                return match ($path) {
                    'vendor-dir' => $this->tmpAbsoluteDir,
                    'home' => getcwd(),
                    default => null,
                };
            });

        $plugin = new Plugin();
        $plugin->activate($this->composer, $this->io);

        static::assertTrue($plugin->isGlobal());
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
}
