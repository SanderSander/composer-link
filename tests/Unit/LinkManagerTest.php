<?php

declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Copyright (c) 2021-2023 Sander Visser <themastersleader@hotmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 *
 * @link https://github.com/SanderSander/composer-link
 */

namespace Tests\Unit;

use Composer\Installer\InstallationManager;
use Composer\Installer\InstallerInterface;
use Composer\Package\CompletePackage;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;
use Composer\Util\Loop;
use ComposerLink\LinkedPackage;
use ComposerLink\LinkManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function React\Promise\resolve;

class LinkManagerTest extends TestCase
{
    /** @var Filesystem&MockObject */
    protected Filesystem $filesystem;

    /** @var InstallerInterface&MockObject */
    protected InstallerInterface $installer;

    /** @var Loop&MockObject */
    protected Loop $loop;

    /** @var LinkedPackage&MockObject */
    protected LinkedPackage $package;

    /** @var InstallationManager|MockObject */
    protected InstallationManager $installationManager;

    /** @var InstalledRepositoryInterface|MockObject */
    protected InstalledRepositoryInterface $installedRepository;

    protected LinkManager $linkManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = $this->createMock(Filesystem::class);
        $this->installer = $this->createMock(InstallerInterface::class);
        $this->loop = $this->createMock(Loop::class);
        $this->package = $this->createMock(LinkedPackage::class);
        $this->installationManager = $this->createMock(InstallationManager::class);
        $this->installedRepository = $this->createMock(InstalledRepositoryInterface::class);

        $this->installationManager->method('getInstaller')->willReturn($this->installer);

        $this->linkManager = new LinkManager(
            $this->filesystem,
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

        static::assertFalse($this->linkManager->isLinked($this->package));
        static::assertTrue($this->linkManager->isLinked($this->package));
        static::assertTrue($this->linkManager->isLinked($this->package));
    }

    public function test_link_without_original_package(): void
    {
        $package = $this->createMock(CompletePackage::class);
        $this->package->method('getPackage')->willReturn($package);

        $this->installer
            ->expects(static::never())
            ->method('uninstall');

        $this->installer
            ->expects(static::once())
            ->method('install')
            ->with($this->installedRepository, $this->package->getPackage())
            ->willReturn(resolve());

        $this->linkManager->linkPackage($this->package);
    }

    public function test_link_with_original_package(): void
    {
        $package = $this->createMock(CompletePackage::class);
        $this->package->method('getPackage')->willReturn($package);

        $original = $this->createMock(PackageInterface::class);
        $this->package->method('getOriginalPackage')
            ->willReturn($original);

        $this->installer
            ->expects(static::once())
            ->method('uninstall')
            ->with($this->installedRepository, $original)
            ->willReturn(resolve(null));

        $this->installer
            ->expects(static::once())
            ->method('install')
            ->with($this->installedRepository, $this->package->getPackage())
            ->willReturn(resolve(null));

        $this->linkManager->linkPackage($this->package);
    }

    public function test_unlink(): void
    {
        $package = $this->createMock(CompletePackage::class);
        $original = $this->createMock(PackageInterface::class);
        $this->package->method('getPackage')->willReturn($package);
        $this->package->method('getOriginalPackage')->willReturn($original);

        $this->installer
            ->expects(static::once())
            ->method('uninstall')
            ->with($this->installedRepository, $package)
            ->willReturn(resolve(null));

        $this->installer
            ->expects(static::once())
            ->method('install')
            ->with($this->installedRepository, $original)
            ->willReturn(resolve(null));

        $this->linkManager->unlinkPackage($this->package);
    }

    public function test_unlink_without_original_package(): void
    {
        $package = $this->createMock(CompletePackage::class);
        $this->package->method('getPackage')->willReturn($package);

        $this->installer
            ->expects(static::once())
            ->method('uninstall')
            ->with($this->installedRepository, $package)
            ->willReturn(resolve(null));

        $this->installer
            ->expects(static::never())
            ->method('install');

        $this->linkManager->unlinkPackage($this->package);
    }

    public function test_is_cleaned_up_after_uninstall_failure(): void
    {
        $package = $this->createMock(CompletePackage::class);
        $this->package->method('getPackage')->willReturn($package);

        $this->installer
            ->expects(static::once())
            ->method('uninstall')
            ->willThrowException(new RuntimeException());

        $this->installer
            ->expects(static::once())
            ->method('cleanup')
            ->with('uninstall', $package);

        $this->installer
            ->expects(static::never())
            ->method('install');

        $this->expectException(RuntimeException::class);
        $this->linkManager->unlinkPackage($this->package);
    }

    public function test_is_cleaned_up_after_install_failure(): void
    {
        $package = $this->createMock(CompletePackage::class);
        $this->package->method('getPackage')->willReturn($package);

        $this->installer
            ->expects(static::never())
            ->method('uninstall');

        $this->installer
            ->expects(static::once())
            ->method('install')
            ->with($this->installedRepository, $this->package->getPackage())
            ->willThrowException(new RuntimeException());

        $this->installer
            ->expects(static::once())
            ->method('cleanup')
            ->with('install', $package);

        $this->expectException(RuntimeException::class);
        $this->linkManager->linkPackage($this->package);
    }
}
