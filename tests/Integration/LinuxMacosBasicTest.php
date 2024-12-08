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

namespace Tests\Integration;

use Composer\Package\Package;

/**
 * @group ubuntu-latest
 * @group macos-latest
 */
class LinuxMacosBasicTest extends TestCase
{
    public function test_upgrade_safety_mechanism(): void
    {
        $this->useComposerLinkLocalOld();

        // Alter composer file so that we update from the current version
        $composerFile = $this->getCurrentComposeFile();
        $composerFile['require']['sandersander/composer-link'] = 'dev-master';
        $composerFile['repositories'] = [[
            'type' => 'path',
            'url' => $this->getThisPackagePath(),
        ]];
        $this->setCurrentComposeFile($composerFile);

        static::assertStringContainsString(
            'Composer link couldn\'t be activated because it was probably upgraded',
            $this->runComposerCommand('update'),
        );
    }

    /**
     * Test if we can link a package in a project while using relative paths.
     * The plugin is installed in project.
     */
    public function test_link_package_in_project_with_relative_paths_with_local_plugin(): void
    {
        $this->useComposerLinkLocal();

        static::assertStringContainsString(
            'No packages are linked',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Installing test/package-1 (dev-linked): Symlinking from ' . $this->getRelativePathToMockDirectory() . '/package-1',
            $this->runComposerCommand('link ' . $this->getRelativePathToMockDirectory() . '/package-1')
        );
        static::assertStringContainsString(
            'test/package-1	' . $this->getRelativePathToMockDirectory() . '/package-1',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Removing test/package-1 (dev-linked)',
            $this->runComposerCommand('unlink ' . $this->getRelativePathToMockDirectory() . '/package-1')
        );
        static::assertStringContainsString(
            'No packages are linked',
            $this->runComposerCommand('linked')
        );
    }

    /**
     * Test if we can link a package in a project while using absolute paths.
     * The plugin is installed in project.
     */
    public function test_link_package_in_project_with_absolute_paths_with_local_plugin(): void
    {
        $this->useComposerLinkLocal();

        static::assertStringContainsString(
            'No packages are linked',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Installing test/package-1 (dev-linked): Symlinking from ' . $this->getMockDirectory() . '/package-1',
            $this->runComposerCommand('link ' . $this->getMockDirectory() . '/package-1')
        );
        static::assertStringContainsString(
            'test/package-1	' . $this->getMockDirectory() . '/package-1',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Removing test/package-1 (dev-linked)',
            $this->runComposerCommand('unlink ' . $this->getMockDirectory() . '/package-1')
        );
        static::assertStringContainsString(
            'No packages are linked',
            $this->runComposerCommand('linked')
        );
    }

    /**
     * Test if we can link a package in a project while using relative paths.
     * The plugin is installed globally.
     */
    public function test_link_package_in_project_with_relative_paths_with_global_plugin(): void
    {
        $this->useComposerLinkGlobal();

        static::assertStringContainsString(
            'No packages are linked',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Installing test/package-1 (dev-linked): Symlinking from ' . $this->getRelativePathToMockDirectory() . '/package-1',
            $this->runComposerCommand('link ' . $this->getRelativePathToMockDirectory() . '/package-1')
        );
        static::assertStringContainsString(
            'test/package-1	' . $this->getRelativePathToMockDirectory() . '/package-1',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Removing test/package-1 (dev-linked)',
            $this->runComposerCommand('unlink ' . $this->getRelativePathToMockDirectory() . '/package-1')
        );
        static::assertStringContainsString(
            'No packages are linked',
            $this->runComposerCommand('linked')
        );
    }

    /**
     * Test if we can link a package in a project while using relative paths.
     * The plugin is installed globally.
     */
    public function test_link_package_in_project_with_absolute_paths_with_global_plugin(): void
    {
        $this->useComposerLinkGlobal();

        static::assertStringContainsString(
            'No packages are linked',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Installing test/package-1 (dev-linked): Symlinking from ' . $this->getMockDirectory() . '/package-1',
            $this->runComposerCommand('link ' . $this->getMockDirectory() . '/package-1')
        );
        static::assertStringContainsString(
            'test/package-1	' . $this->getMockDirectory() . '/package-1',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Removing test/package-1 (dev-linked)',
            $this->runComposerCommand('unlink ' . $this->getMockDirectory() . '/package-1')
        );
        static::assertStringContainsString(
            'No packages are linked',
            $this->runComposerCommand('linked')
        );
    }

    /**
     * Test if we can link a package globally while using relative paths.
     * The plugin is installed globally.
     */
    public function test_link_package_in_global_with_relative_paths_with_global_plugin(): void
    {
        $this->useComposerLinkGlobal();

        static::assertStringContainsString(
            'No packages are linked',
            $this->runComposerCommand('global linked')
        );
        static::assertStringContainsString(
            '  - Installing test/package-1 (dev-linked): Symlinking from ' . $this->getMockDirectory() . '/package-1',
            $this->runComposerCommand('global link ' . $this->getRelativePathToMockDirectory() . '/package-1')
        );
        static::assertStringContainsString(
            'test/package-1	' . $this->getMockDirectory() . '/package-1',
            $this->runComposerCommand('global linked')
        );
        static::assertStringContainsString(
            '  - Removing test/package-1 (dev-linked)',
            $this->runComposerCommand('global unlink ' . $this->getRelativePathToMockDirectory() . '/package-1')
        );
        static::assertStringContainsString(
            'No packages are linked',
            $this->runComposerCommand('global linked')
        );
    }

    /**
     * Test if we can link a package globally while using absolute paths.
     * The plugin is installed globally.
     */
    public function test_link_package_in_global_with_absolute_paths_with_global_plugin(): void
    {
        $this->useComposerLinkGlobal();

        static::assertStringContainsString(
            'No packages are linked',
            $this->runComposerCommand('global linked')
        );
        static::assertStringContainsString(
            '  - Installing test/package-1 (dev-linked): Symlinking from ' . $this->getMockDirectory() . '/package-1',
            $this->runComposerCommand('global link ' . $this->getMockDirectory() . '/package-1')
        );
        static::assertStringContainsString(
            'test/package-1	' . $this->getMockDirectory() . '/package-1',
            $this->runComposerCommand('global linked')
        );
        static::assertStringContainsString(
            '  - Removing test/package-1 (dev-linked)',
            $this->runComposerCommand('global unlink ' . $this->getMockDirectory() . '/package-1')
        );
        static::assertStringContainsString(
            'No packages are linked',
            $this->runComposerCommand('global linked')
        );
    }
}
