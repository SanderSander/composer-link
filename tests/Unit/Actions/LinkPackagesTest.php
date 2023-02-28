<?php

declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Copyright (c) 2021-2023 Sander Visser <themastersleader@hotmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 *
 * @link https://github.com/SanderSander/composer-link
 */

namespace Tests\Unit\Actions;

use Composer\Repository\RepositoryManager;
use ComposerLink\Actions\LinkPackages;
use ComposerLink\LinkManager;
use ComposerLink\Repository\Repository;
use Tests\Unit\TestCase;

class LinkPackagesTest extends TestCase
{
    public function test_if_packages_are_linked(): void
    {
        $repository = $this->createMock(Repository::class);
        $linkManger = $this->createMock(LinkManager::class);
        $repositoryManager = $this->createMock(RepositoryManager::class);
        $package1 = $this->mockPackage('package-1');
        $package2 = $this->mockPackage('package-2');

        $repository->method('all')->willReturn([$package1, $package2]);
        $linkManger->method('isLinked')->willReturnOnConsecutiveCalls(false, true);
        $linkManger->expects(static::once())->method('linkPackage')->with($package1);

        $link = new LinkPackages(
            $linkManger,
            $repository,
            $repositoryManager
        );
        $link->execute();
    }
}
