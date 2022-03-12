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
}
