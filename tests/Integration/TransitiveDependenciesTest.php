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
 * @group ubuntu-latest
 * @group macos-latest
 */
class TransitiveDependenciesTest extends TestCase
{
    public function test_link_with_transitive_dependencies(): void
    {
        $this->useComposerLinkGlobal();

        $composerFile = [
            'repositories' => [
                [
                    'type' => 'path',
                    'url' => $this->getMockDirectory() . '/package-2',
                ],
            ],
        ];
        $this->setCurrentComposeFile($composerFile);

        // Require test/package-2 with dependency to psr/container 2.0.1
        static::assertStringContainsString(
            'Installing psr/container (2.0.1): Extracting archive',
            $this->runComposerCommand('require test/package-2 @dev')
        );

        static::assertStringContainsString(
            'Installing psr/container (dev-linked): Symlinking from',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/psr-container'),
        );

        // Unlink and test if 2.0.1 is installed again
        static::assertStringContainsString(
            'Installing psr/container (2.0.1): Extracting archive',
            $this->runComposerCommand('unlink ' . self::RELATIVE_PATH_MOCK . '/psr-container'),
        );
    }

    public function test_link_with_dev_dependencies(): void
    {
        $this->useComposerLinkGlobal();

        $composerFile = [
            'repositories' => [
                [
                    'type' => 'path',
                    'url' => $this->getMockDirectory() . '/package-4',
                ],
            ],
            'minimum-stability' => 'dev',
        ];
        $this->setCurrentComposeFile($composerFile);

        // Require test/package-2 with a dependency to psr/container dev-master
        static::assertStringContainsString(
            'Installing psr/container (dev-master',
            $this->runComposerCommand('require test/package-4 @dev')
        );

        static::assertStringContainsString(
            'Installing psr/container (dev-linked): Symlinking from',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/psr-container'),
        );

        // Unlink and test if dev-master is installed again
        static::assertStringContainsString(
            'Installing psr/container (dev-master',
            $this->runComposerCommand('unlink ' . self::RELATIVE_PATH_MOCK . '/psr-container'),
        );
    }

    public function test_link_without_dependencies(): void
    {
        $this->useComposerLinkGlobal();

        static::assertStringContainsString(
            'Installing test/package-3 (dev-linked): Symlinking from',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/package-3'),
        );

        static::assertStringContainsString(
            'Installing test/package-5 (dev-linked): Symlinking from',
            $this->runComposerCommand('link --without-dependencies ' . self::RELATIVE_PATH_MOCK . '/package-5'),
        );
    }

    public function test_link_with_transitive_dev_dependencies(): void
    {
        $this->useComposerLinkGlobal();

        static::assertStringContainsString(
            'Installing test/package-3 (dev-linked): Symlinking from',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/package-3'),
        );

        static::assertStringContainsString(
            'Installing test/package-5 (dev-linked): Symlinking from',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/package-5'),
        );
    }

    public function test_link_with_transitive_dependencies_local(): void
    {
        $this->useComposerLinkGlobal();
        $this->setCurrentComposeFile([
            'repositories' => [
                [
                    'type' => 'path',
                    'url' => $this->getMockDirectory() . '/package-3',
                ],
            ],
            'minimum-stability' => 'dev',
        ]);

        static::assertStringContainsString(
            'Installing test/package-5 (dev-linked): Symlinking from',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/package-5'),
        );

        static::assertStringContainsString(
            'Upgrading test/package-3 (dev-main => dev-linked): Source already present',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/package-3'),
        );
    }

    public function test_link_with_transitive_dependencies_non_local(): void
    {
        $this->useComposerLinkGlobal();
        $this->setCurrentComposeFile([
            'minimum-stability' => 'dev',
        ]);

        static::assertStringContainsString(
            'Installing psr/http-factory (dev-linked): Symlinking from',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/http-factory'),
        );

        static::assertStringContainsString(
            'Installing psr/http-message (dev-linked): Symlinking from',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/http-message'),
        );

        static::assertStringContainsString(
            'Installing test/package-6 (dev-linked): Symlinking from',
            $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/package-6'),
        );
    }
}
