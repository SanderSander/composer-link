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

/**
 * @group windows-latest
 */
class WindowsBasicTest extends TestCase
{
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
            '  - Installing test/package-1 (dev-linked): Junctioning from ' . self::RELATIVE_PATH_MOCK . '\package-1',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '\package-1')
        );
        static::assertStringContainsString(
            'test/package-1	' . self::RELATIVE_PATH_MOCK . '\package-1',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Removing test/package-1 (dev-linked), source is still present in ' . $this->tmpAbsoluteDir . 'vendor/test/package-1',
            $this->runComposerCommand('unlink ' . self::RELATIVE_PATH_MOCK . '\package-1')
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
            '  - Installing test/package-1 (dev-linked): Junctioning from ' . $this->getMockDirectory() . '\package-1',
            $this->runComposerCommand('link ' . $this->getMockDirectory() . '\package-1')
        );
        static::assertStringContainsString(
            'test/package-1	' . $this->getMockDirectory() . '\package-1',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Removing test/package-1 (dev-linked), source is still present in ' . $this->tmpAbsoluteDir . 'vendor/test/package-1',
            $this->runComposerCommand('unlink ' . $this->getMockDirectory() . '\package-1')
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
            '  - Installing test/package-1 (dev-linked): Junctioning from ' . self::RELATIVE_PATH_MOCK . '\package-1',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '\package-1')
        );
        static::assertStringContainsString(
            'test/package-1	' . self::RELATIVE_PATH_MOCK . '\package-1',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Removing test/package-1 (dev-linked), source is still present in ' . $this->composerGlobalDir . '\vendor/test/package-1',
            $this->runComposerCommand('unlink ' . self::RELATIVE_PATH_MOCK . '\package-1')
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
            '  - Installing test/package-1 (dev-linked): Junctioning from ' . $this->getMockDirectory() . '\package-1',
            $this->runComposerCommand('link ' . $this->getMockDirectory() . '\package-1')
        );
        static::assertStringContainsString(
            'test/package-1	' . $this->getMockDirectory() . '\package-1',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Removing test/package-1 (dev-linked), source is still present in ' . $this->composerGlobalDir . '\vendor/test/package-1',
            $this->runComposerCommand('unlink ' . $this->getMockDirectory() . '\package-1')
        );
        static::assertStringContainsString(
            'No packages are linked',
            $this->runComposerCommand('linked')
        );
    }
}
