<?php

declare(strict_types=1);

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

namespace Tests\Integration;

/**
 * @group linux-latest
 * @group macos-latest
 */
class LinuxMacosBasicTest extends TestCase
{
    /**
     * Test if we can link a package in a project while using relative paths.
     * The plugin is installed in project.
     */
    public function test_link_package_in_project_with_relative_paths_with_local_plugin(): void
    {
        $this->useComposerLinkLocal();

        static::assertContains(
            'No packages are linked',
            $this->runLinkCommand('linked')
        );
        static::assertContains(
            '  - Installing test/package-1 (dev-master): Symlinking from ../mock/package-1',
            $this->runLinkCommand('link ../mock/package-1')
        );
        static::assertContains(
            'test/package-1	../mock/package-1',
            $this->runLinkCommand('linked')
        );
        static::assertContains(
            '  - Removing test/package-1 (dev-master)',
            $this->runLinkCommand('unlink ../mock/package-1')
        );
        static::assertContains(
            'No packages are linked',
            $this->runLinkCommand('linked')
        );
    }

    /**
     * Test if we can link a package in a project while using absolute paths.
     * The plugin is installed in project.
     */
    public function test_link_package_in_project_with_absolute_paths_with_local_plugin(): void
    {
        $this->useComposerLinkLocal();

        static::assertContains(
            'No packages are linked',
            $this->runLinkCommand('linked')
        );
        static::assertContains(
            '  - Installing test/package-1 (dev-master): Symlinking from ' . $this->getMockDirectory() . '/package-1',
            $this->runLinkCommand('link ' . $this->getMockDirectory() . '/package-1')
        );
        static::assertContains(
            'test/package-1	' . $this->getMockDirectory() . '/package-1',
            $this->runLinkCommand('linked')
        );
        static::assertContains(
            '  - Removing test/package-1 (dev-master)',
            $this->runLinkCommand('unlink ' . $this->getMockDirectory() . '/package-1')
        );
        static::assertContains(
            'No packages are linked',
            $this->runLinkCommand('linked')
        );
    }

    /**
     * Test if we can link a package in a project while using relative paths.
     * The plugin is installed globally.
     */
    public function test_link_package_in_project_with_relative_paths_with_global_plugin(): void
    {
        $this->useComposerLinkGlobal();

        static::assertContains(
            'No packages are linked',
            $this->runLinkCommand('linked')
        );
        static::assertContains(
            '  - Installing test/package-1 (dev-master): Symlinking from ../mock/package-1',
            $this->runLinkCommand('link ../mock/package-1')
        );
        static::assertContains(
            'test/package-1	../mock/package-1',
            $this->runLinkCommand('linked')
        );
        static::assertContains(
            '  - Removing test/package-1 (dev-master)',
            $this->runLinkCommand('unlink ../mock/package-1')
        );
        static::assertContains(
            'No packages are linked',
            $this->runLinkCommand('linked')
        );
    }

    /**
     * Test if we can link a package in a project while using relative paths.
     * The plugin is installed globally.
     */
    public function test_link_package_in_project_with_absolute_paths_with_global_plugin(): void
    {
        $this->useComposerLinkGlobal();

        static::assertContains(
            'No packages are linked',
            $this->runLinkCommand('linked')
        );
        static::assertContains(
            '  - Installing test/package-1 (dev-master): Symlinking from ' . $this->getMockDirectory() . '/package-1',
            $this->runLinkCommand('link ' . $this->getMockDirectory() . '/package-1')
        );
        static::assertContains(
            'test/package-1	' . $this->getMockDirectory() . '/package-1',
            $this->runLinkCommand('linked')
        );
        static::assertContains(
            '  - Removing test/package-1 (dev-master)',
            $this->runLinkCommand('unlink ' . $this->getMockDirectory() . '/package-1')
        );
        static::assertContains(
            'No packages are linked',
            $this->runLinkCommand('linked')
        );
    }

    /**
     * Test if we can link a package globally while using relative paths.
     * The plugin is installed globally.
     */
    public function test_link_package_in_global_with_relative_paths_with_global_plugin(): void
    {
        $this->useComposerLinkGlobal();

        static::assertContains(
            'No packages are linked',
            $this->runLinkCommand('global linked')
        );
        static::assertContains(
            '  - Installing test/package-1 (dev-master): Symlinking from ' . $this->getMockDirectory() . '/package-1',
            $this->runLinkCommand('global link ../mock/package-1')
        );
        static::assertContains(
            'test/package-1	' . $this->getMockDirectory() . '/package-1',
            $this->runLinkCommand('global linked')
        );
        static::assertContains(
            '  - Removing test/package-1 (dev-master)',
            $this->runLinkCommand('global unlink ../mock/package-1')
        );
        static::assertContains(
            'No packages are linked',
            $this->runLinkCommand('global linked')
        );
    }

    /**
     * Test if we can link a package globally while using absolute paths.
     * The plugin is installed globally.
     */
    public function test_link_package_in_global_with_absolute_paths_with_global_plugin(): void
    {
        $this->useComposerLinkGlobal();

        static::assertContains(
            'No packages are linked',
            $this->runLinkCommand('global linked')
        );
        static::assertContains(
            '  - Installing test/package-1 (dev-master): Symlinking from ' . $this->getMockDirectory() . '/package-1',
            $this->runLinkCommand('global link ' . $this->getMockDirectory() . '/package-1')
        );
        static::assertContains(
            'test/package-1	' . $this->getMockDirectory() . '/package-1',
            $this->runLinkCommand('global linked')
        );
        static::assertContains(
            '  - Removing test/package-1 (dev-master)',
            $this->runLinkCommand('global unlink ' . $this->getMockDirectory() . '/package-1')
        );
        static::assertContains(
            'No packages are linked',
            $this->runLinkCommand('global linked')
        );
    }
}
