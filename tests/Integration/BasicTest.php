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

class BasicTest extends TestCase
{
    public function test_package_can_be_linked_and_unlinked(): void
    {
        $this->useComposerLinkLocal();

        $linkType = PHP_OS_FAMILY === 'Windows' ? 'Junctioning' : 'Symlinking';

        static::assertContains(
            'No packages are linked',
            $this->runLinkCommand('linked')
        );
        static::assertContains(
            '  - Installing test/package-1 (dev-master): ' . $linkType . ' from ../mock/package-1',
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
}
