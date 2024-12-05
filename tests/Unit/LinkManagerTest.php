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

use Composer\Installer\InstallationManager;
use Composer\Installer\InstallerInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;
use Composer\Util\Loop;
use ComposerLink\LinkManager;
use ComposerLink\Package\LinkedPackage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
    }

    public function test_todo(): void
    {
        static::assertTrue(true);
    }
}
