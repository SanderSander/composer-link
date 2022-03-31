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

use Composer\Downloader\DownloaderInterface;
use Composer\Downloader\DownloadManager;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackage;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->downloadManager = $this->createMock(DownloadManager::class);
        $this->loop = $this->createMock(Loop::class);
        $this->package = $this->createMock(LinkedPackage::class);
    }

    public function test_is_linked(): void
    {
        $manager = new LinkManager(
            $this->filesystem,
            $this->io,
            $this->downloadManager,
            $this->loop
        );

        $this->filesystem->method('isSymlinkedDirectory')
            ->willReturnOnConsecutiveCalls(false, true, false);
        // Short circuit, so we only expect 2 calls
        $this->filesystem->method('isJunction')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->assertFalse($manager->isLinked($this->package));
        $this->assertTrue($manager->isLinked($this->package));
        $this->assertTrue($manager->isLinked($this->package));
    }

    public function test_link(): void
    {
        $manager = new LinkManager(
            $this->filesystem,
            $this->io,
            $this->downloadManager,
            $this->loop
        );

        $package = $this->createMock(CompletePackage::class);
        $this->package->method('getPackage')->willReturn($package);

        $pathDownloader = $this->createMock(DownloaderInterface::class);
        $pathDownloader->expects($this->once())
            ->method('install')
            ->with($package, $this->package->getInstallationPath());
        $this->downloadManager->method('getDownloader')
            ->with('path')
            ->willReturn($pathDownloader);

        $manager->linkPackage($this->package);
    }

    public function test_unlink(): void
    {
        $manager = new LinkManager(
            $this->filesystem,
            $this->io,
            $this->downloadManager,
            $this->loop
        );

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

        $this->downloadManager
            ->expects($this->once())
            ->method('install');

        $manager->unlinkPackage($this->package);
    }

    public function test_unlink_without_original_package(): void
    {
        $manager = new LinkManager(
            $this->filesystem,
            $this->io,
            $this->downloadManager,
            $this->loop
        );

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

        $manager->unlinkPackage($this->package);
    }
}
