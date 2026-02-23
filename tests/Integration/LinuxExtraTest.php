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
class LinuxExtraTest extends TestCase
{
    public function test_upgrade_safety_mechanism(): void
    {
        $this->useComposerLinkLocalOld();

        // Alter composer file so that we update from the current version
        $composerFile = $this->getCurrentComposeFile();
        $composerFile['require']['sandersander/composer-link'] = '@dev';
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

    public function test_link_with_dependencies(): void
    {
        $this->useComposerLinkLocal();
        $output = $this->runComposerCommand('link ' . self::RELATIVE_PATH_MOCK . '/package-2');

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
