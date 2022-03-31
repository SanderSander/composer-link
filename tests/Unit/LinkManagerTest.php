<?php declare(strict_types=1);

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

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\Downloader\DownloaderInterface;
use Composer\Downloader\DownloadManager;
use Composer\Installer\InstallationManager;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackage;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;
use Composer\Util\Loop;
use ComposerLink\LinkedPackage;
use ComposerLink\LinkManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkManagerTest extends TestCase
{
    /** @var Filesystem&MockObject  */
    protected Filesystem $filesystem;

    /** @var IOInterface&MockObject  */
    protected IOInterface $io;

    /** @var DownloadManager&MockObject  */
    protected DownloadManager $downloadManager;

    /** @var Loop&MockObject  */
    protected Loop $loop;

    /** @var LinkedPackage&MockObject  */
    protected LinkedPackage $package;

    /** @var InstallationManager|MockObject  */
    protected InstallationManager $installationManager;

    /** @var InstalledRepositoryInterface|MockObject  */
    protected InstalledRepositoryInterface $installedRepository;

    protected LinkManager $linkManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = $this->createMock(Filesystem::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->downloadManager = $this->createMock(DownloadManager::class);
        $this->loop = $this->createMock(Loop::class);
        $this->package = $this->createMock(LinkedPackage::class);
        $this->installationManager = $this->createMock(InstallationManager::class);
        $this->installedRepository = $this->createMock(InstalledRepositoryInterface::class);

        $this->linkManager = new LinkManager(
            $this->filesystem,
            $this->io,
            $this->downloadManager,
            $this->loop,
            $this->installationManager,
            $this->installedRepository
        );
    }

    public function test_is_linked(): void
    {
        $this->filesystem->method('isSymlinkedDirectory')
            ->willReturnOnConsecutiveCalls(false, true, false);
        // Short circuit, so we only expect 2 calls
        $this->filesystem->method('isJunction')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->assertFalse($this->linkManager->isLinked($this->package));
        $this->assertTrue($this->linkManager->isLinked($this->package));
        $this->assertTrue($this->linkManager->isLinked($this->package));
    }

    public function test_link_without_original_package(): void
    {
        $package = $this->createMock(CompletePackage::class);
        $this->package->method('getPackage')->willReturn($package);

        $this->installationManager
            ->expects($this->never())
            ->method('uninstall');

        $this->installationManager
            ->expects($this->once())
            ->method('install')
            ->with($this->installedRepository, new InstallOperation($this->package->getPackage()));

        $this->linkManager->linkPackage($this->package);
    }

    public function test_link_with_original_package(): void
    {
        $package = $this->createMock(CompletePackage::class);
        $this->package->method('getPackage')->willReturn($package);

        $original = $this->createMock(PackageInterface::class);
        $this->package->method('getOriginalPackage')
            ->willReturn($original);

        $this->installationManager
            ->expects($this->once())
            ->method('uninstall')
            ->with($this->installedRepository, new UninstallOperation($original));

        $this->installationManager
            ->expects($this->once())
            ->method('install')
            ->with($this->installedRepository, new InstallOperation($this->package->getPackage()));

        $this->linkManager->linkPackage($this->package);
    }

    public function test_unlink(): void
    {
        $package = $this->createMock(CompletePackage::class);
        $this->package->method('getPackage')->willReturn($package);
        $this->package->method('getOriginalPackage')->willReturn($package);

        $pathDownloader = $this->createMock(DownloaderInterface::class);
        $pathDownloader->expects($this->once())
            ->method('remove')
            ->with($package, $this->package->getInstallationPath());

        $this->downloadManager
            ->method('getDownloader')
            ->with('path')
            ->willReturn($pathDownloader);

        $this->installationManager
            ->expects($this->once())
            ->method('install');

        $this->linkManager->unlinkPackage($this->package);
    }

    public function test_unlink_without_original_package(): void
    {
        $package = $this->createMock(CompletePackage::class);
        $this->package->method('getPackage')->willReturn($package);
        $this->package->method('getOriginalPackage')->willReturn(null);

        $pathDownloader = $this->createMock(DownloaderInterface::class);
        $pathDownloader->expects($this->once())
            ->method('remove')
            ->with($package, $this->package->getInstallationPath());

        $this->downloadManager
            ->method('getDownloader')
            ->with('path')
            ->willReturn($pathDownloader);

        $this->downloadManager
            ->expects($this->never())
            ->method('install');

        $this->linkManager->unlinkPackage($this->package);
    }
}
