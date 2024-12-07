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
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\RepositoryManager;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use ComposerLink\CommandProvider;
use ComposerLink\LinkManager;
use ComposerLink\LinkManagerFactory;
use ComposerLink\Plugin;
use ComposerLink\Repository\Repository;
use ComposerLink\Repository\RepositoryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use TypeError;

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

    /**
     * @var InstalledRepositoryInterface&MockObject
     */
    protected InstalledRepositoryInterface $localRepository;

    /**
     * @var Repository&MockObject
     */
    protected Repository $repository;

    /**
     * @var LinkManager&MockObject
     */
    protected LinkManager $linkManager;

    protected Plugin $plugin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->io = $this->createMock(IOInterface::class);
        $installationManager = $this->createMock(InstallationManager::class);
        $this->config = $this->createMock(Config::class);
        $repositoryManager = $this->createMock(RepositoryManager::class);
        $this->localRepository = $this->createMock(InstalledRepositoryInterface::class);
        $repositoryManager->method('getLocalRepository')->willReturn($this->localRepository);
        $repositoryFactory = $this->createMock(RepositoryFactory::class);
        $this->repository = $this->createMock(Repository::class);
        $repositoryFactory->method('create')->willReturn($this->repository);

        $this->composer = $this->createMock(Composer::class);
        $this->composer->method('getInstallationManager')->willReturn($installationManager);
        $this->composer->method('getConfig')->willReturn($this->config);
        $this->composer->method('getRepositoryManager')->willReturn($repositoryManager);

        $this->linkManager = $this->createMock(LinkManager::class);
        $linkManagerFactory = $this->createMock(LinkManagerFactory::class);
        $linkManagerFactory->method('create')->willReturn($this->linkManager);

        $this->plugin = new Plugin(
            $this->filesystem,
            $repositoryFactory,
            $linkManagerFactory,
        );
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

        $this->plugin->activate($this->composer, $this->io);

        $capabilities = $this->plugin->getCapabilities();
        $events = Plugin::getSubscribedEvents();

        static::assertArrayHasKey(ComposerCommandProvider::class, $capabilities);
        static::assertContains(CommandProvider::class, $capabilities);
        static::assertArrayHasKey(ScriptEvents::POST_UPDATE_CMD, $events);
        static::assertArrayHasKey(ScriptEvents::POST_INSTALL_CMD, $events);
        static::assertFalse($this->plugin->isGlobal());

        $this->plugin->getPackageFactory();
        $this->plugin->getLinkManager();
        $this->plugin->getRepository();
        $this->plugin->deactivate($this->composer, $this->io);
        $this->plugin->uninstall($this->composer, $this->io);
    }

    public function test_unable_to_activate_plugin(): void
    {
        $repositoryFactory = $this->createMock(RepositoryFactory::class);
        $linkManagerFactory = $this->createMock(LinkManagerFactory::class);
        $event = $this->createMock(Event::class);
        $event->method('getIO')->willReturn($this->io);

        $repositoryFactory->method('create')
            ->willThrowException(new TypeError('test error'));

        $plugin = new Plugin(
            $this->filesystem,
            $repositoryFactory,
            $linkManagerFactory,
        );

        $plugin->activate($this->composer, $this->io);

        $this->io->expects(static::once())->method('warning')->with(
            static::stringContains('Composer link couldn\'t be activated')
        );
        $plugin->postUpdate($event);
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

        $this->plugin->activate($this->composer, $this->io);

        static::assertTrue($this->plugin->isGlobal());
    }

    public function test_post_install(): void
    {
        $this->plugin->activate($this->composer, $this->io);
        $event = $this->createMock(Event::class);

        $this->linkManager->method('hasLinkedPackages')->willReturn(true);
        $this->linkManager->expects(static::once())->method('linkPackages');
        $this->plugin->postInstall($event);
    }

    public function test_post_update(): void
    {
        $this->plugin->activate($this->composer, $this->io);
        $event = $this->createMock(Event::class);
        $package = $this->mockPackage();
        $original = $this->mockPackage('original');

        $this->localRepository->method('findPackage')->willReturn($original);
        $this->linkManager->method('hasLinkedPackages')->willReturn(true);

        $this->repository->method('all')->willReturn([$package]);
        $package->expects(static::once())->method('setOriginalPackage')->with($original);

        $this->linkManager->expects(static::once())->method('linkPackages');
        $this->plugin->postUpdate($event);
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

    public function test_plugin_throws_exception_initialize_manager(): void
    {
        self::expectException(RuntimeException::class);
        $plugin = new Plugin();
        $plugin->getLinkManager();
    }
}
