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
use Composer\DependencyResolver\Request;
use Composer\Filter\PlatformRequirementFilter\IgnoreAllPlatformRequirementFilter;
use Composer\Installer;
use Composer\IO\IOInterface;
use Composer\Package\Link;
use Composer\Package\Locker;
use Composer\Package\Package;
use Composer\Package\RootPackageInterface;
use Composer\Repository\LockArrayRepository;
use ComposerLink\InstallerFactory;
use ComposerLink\LinkManager;
use ComposerLink\Repository\Repository;
use PHPUnit\Framework\MockObject\MockObject;

class LinkManagerTest extends TestCase
{
    /**
     * @var Repository&MockObject
     */
    protected Repository $repository;

    /**
     * @var Installer&MockObject
     */
    protected Installer $installer;

    /**
     * @var IOInterface&MockObject
     */
    protected IOInterface $io;

    /**
     * @var Composer&MockObject
     */
    protected Composer $composer;

    /**
     * @var RootPackageInterface&MockObject
     */
    protected RootPackageInterface $rootPackage;

    /**
     * @var LockArrayRepository&MockObject
     */
    protected LockArrayRepository $lockArrayRepository;

    protected LinkManager $linkManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(Repository::class);
        $installerFactory = $this->createMock(InstallerFactory::class);
        $this->installer = $this->createMock(Installer::class);
        $installerFactory->method('create')->willReturn($this->installer);
        $this->io = $this->createMock(IOInterface::class);
        $this->composer = $this->createMock(Composer::class);
        $this->rootPackage = $this->createMock(RootPackageInterface::class);
        $this->composer->method('getPackage')->willReturn($this->rootPackage);

        $locker = $this->createMock(Locker::class);
        $this->lockArrayRepository = $this->createMock(LockArrayRepository::class);
        $locker->method('getLockedRepository')->willReturn($this->lockArrayRepository);
        $this->composer->method('getLocker')->willReturn($locker);

        $this->linkManager = new LinkManager(
            $this->repository,
            $installerFactory,
            $this->io,
            $this->composer,
        );
    }

    public function test_has_linked_packages(): void
    {
        static::assertFalse($this->linkManager->hasLinkedPackages());
        $this->linkManager->add($this->mockPackage());
        static::assertTrue($this->linkManager->hasLinkedPackages());
    }

    public function test_loads_active_linked_packages(): void
    {
        $installerFactory = $this->createMock(InstallerFactory::class);
        $installerFactory->method('create')->willReturn($this->installer);
        $this->repository->method('all')->willReturn([$this->mockPackage()]);

        $linkManager = new LinkManager(
            $this->repository,
            $installerFactory,
            $this->io,
            $this->composer,
        );
        static::assertTrue($linkManager->hasLinkedPackages());
    }

    public function test_add_package(): void
    {
        $package = $this->mockPackage();

        $this->repository->expects(static::once())->method('store')->with($package);
        $this->repository->expects(static::once())->method('persist');

        $this->linkManager->add($package);
    }

    public function test_remove_package(): void
    {
        $package = $this->mockPackage();

        $this->repository->expects(static::once())->method('remove')->with($package);
        $this->repository->expects(static::once())->method('persist');

        $this->linkManager->remove($package);
    }

    public function test_link_packages_empty(): void
    {
        $this->installer->expects(static::once())->method('setUpdate')->with(false)->willReturnSelf();
        $this->installer->expects(static::once())->method('setInstall')->with(true)->willReturnSelf();
        $this->installer->expects(static::once())->method('setWriteLock')->with(false)->willReturnSelf();
        $this->installer->expects(static::once())->method('setRunScripts')->with(false)->willReturnSelf();
        $this->installer->expects(static::once())->method('setUpdateAllowList')->with([])->willReturnSelf();
        $this->installer->expects(static::once())->method('setDevMode')->with(false)->willReturnSelf();
        $this->installer->expects(static::once())->method('setPlatformRequirementFilter')->with(new IgnoreAllPlatformRequirementFilter())->willReturnSelf();
        $this->installer->expects(static::once())->method('setUpdateAllowTransitiveDependencies')->with(Request::UPDATE_ONLY_LISTED)->willReturnSelf();
        $this->installer->expects(static::once())->method('run');

        $this->linkManager->linkPackages(false);
    }

    public function test_link_packages(): void
    {
        $package = $this->mockPackage();
        $link = $this->createMock(Link::class);
        $package->method('createLink')->willReturn($link);
        $this->linkManager->add($package);

        $this->rootPackage->expects(static::once())->method('setRequires')->with(['test/package' => $link]);
        $this->rootPackage->expects(static::once())->method('setDevRequires')->with([]);
        $this->installer->expects(static::once())->method('setUpdate')->with(true)->willReturnSelf();
        $this->installer->expects(static::once())->method('setInstall')->with(true)->willReturnSelf();
        $this->installer->expects(static::once())->method('setWriteLock')->with(false)->willReturnSelf();
        $this->installer->expects(static::once())->method('setRunScripts')->with(false)->willReturnSelf();
        $this->installer->expects(static::once())->method('setUpdateAllowList')->with(['test/package'])->willReturnSelf();
        $this->installer->expects(static::once())->method('setDevMode')->with(true)->willReturnSelf();
        $this->installer->expects(static::once())->method('setPlatformRequirementFilter')->with(new IgnoreAllPlatformRequirementFilter())->willReturnSelf();
        $this->installer->expects(static::once())->method('setUpdateAllowTransitiveDependencies')->with(Request::UPDATE_ONLY_LISTED)->willReturnSelf();
        $this->installer->expects(static::once())->method('run');

        $this->linkManager->linkPackages(true);
    }

    public function test_override_from_dev_requirements(): void
    {
        $package = $this->mockPackage();
        $link = $this->createMock(Link::class);
        $package->method('createLink')->willReturn($link);
        $this->linkManager->add($package);

        $this->rootPackage->method('getDevRequires')->willReturn(['test/package' => $link]);
        $this->rootPackage->expects(static::once())->method('setRequires')->with(['test/package' => $link]);
        $this->rootPackage->expects(static::once())->method('setDevRequires')->with([]);

        $this->linkManager->linkPackages(true);
    }

    public function test_creates_alias_package(): void
    {
        $package = $this->mockPackage();
        $locked = $this->createMock(Package::class);

        $this->lockArrayRepository->expects(static::once())
            ->method('findPackage')
            ->with($package->getName())
            ->willReturn($locked);

        $this->repository->expects(static::once())->method('store')->with($package);
        $this->repository->expects(static::exactly(2))->method('persist');

        // Add package and remove again
        $this->linkManager->add($package);
        static::assertTrue($this->linkManager->hasLinkedPackages());
        $this->linkManager->remove($package);
        static::assertFalse($this->linkManager->hasLinkedPackages());
    }
}
