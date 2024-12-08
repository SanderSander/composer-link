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
 */
class LinuxWithDependenciesTest extends TestCase
{
    public function test_link_with_dependencies(): void
    {
        $this->useComposerLinkLocal();
        $output = $this->runComposerCommand('link ' . $this->getRelativePathToMockDirectory() . '/package-2');

        static::assertStringContainsString(
            'Installing psr/container (2.0.1): Extracting archive',
            $output
        );
        static::assertStringContainsString(
            ' Installing test/package-2 (dev-linked)',
            $output
        );
    }
}
