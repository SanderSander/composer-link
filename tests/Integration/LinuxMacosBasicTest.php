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

use PHPUnit\Framework\Attributes\Group;

#[Group('ubuntu-latest')]
#[Group('macos-latest')]
class LinuxMacosBasicTest extends TestCase
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
            '  - Installing test/package-1 (dev-linked): Symlinking from ' . self::RELATIVE_PATH_MOCK . '/package-1',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/package-1')
        );
        static::assertStringContainsString(
            'test/package-1	' . self::RELATIVE_PATH_MOCK . '/package-1',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Removing test/package-1 (dev-linked)',
            $this->runComposerCommand('unlink ' . self::RELATIVE_PATH_MOCK . '/package-1')
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
            '  - Installing test/package-1 (dev-linked): Symlinking from ' . self::RELATIVE_PATH_MOCK . '/package-1',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/package-1')
        );
        static::assertStringContainsString(
            'test/package-1	' . self::RELATIVE_PATH_MOCK . '/package-1',
            $this->runComposerCommand('linked')
        );
        static::assertStringContainsString(
            '  - Removing test/package-1 (dev-linked)',
            $this->runComposerCommand('unlink ' . self::RELATIVE_PATH_MOCK . '/package-1')
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
            $this->runComposerCommand('global link ' . self::RELATIVE_PATH_MOCK . '/package-1')
        );
        static::assertStringContainsString(
            'test/package-1	' . $this->getMockDirectory() . '/package-1',
            $this->runComposerCommand('global linked')
        );
        static::assertStringContainsString(
            '  - Removing test/package-1 (dev-linked)',
            $this->runComposerCommand('global unlink ' . self::RELATIVE_PATH_MOCK . '/package-1')
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

    /**
     * Test if we can link a package in a project while using absolute paths.
     */
    public function test_composer_linked_forwards_pre_pool_create_to_other_plugins(): void
    {
        $this->useComposerLinkLocal();
        $composerJson = $this->getCurrentComposeFile();

        $composerJson['repositories'][] = [
            'type' => 'path',
            'url' => __DIR__ . '/../mock/PluginEventListenerMock',
            'options' => [
                'symlink' => false,
            ],
        ];
        $composerJson['require-dev']['test/plugin-event-listener'] = 'dev-master';
        $composerJson['config']['allow-plugins']['test/plugin-event-listener'] = true;
        $this->setCurrentComposeFile($composerJson);

        // Install the plugin
        $exitCode = 0;
        $output = $this->runComposerCommand('update', $exitCode);
        static::assertSame(0, $exitCode, $output);
        $exitCode = 0;
        $output = $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/package-1', $exitCode);
        static::assertSame(0, $exitCode, $output);

        // Other plugins should have received the event
        $count = (int) file_get_contents('pre-pool-create-fired.txt');
        static::assertSame(1, $count, $output);

        // Test events
        $output = $this->runComposerCommand('install', $exitCode);
        static::assertSame(0, $exitCode, $output);
        static::assertFileExists('pre-pool-create-fired.txt', $output);

        // Other plugins should have received the event twice (plus 1 time on the link)
        // Once for the normal installation, and once for the linking the packages.
        $count = (int) file_get_contents('pre-pool-create-fired.txt');
        static::assertSame(3, $count, $output);
    }
}
