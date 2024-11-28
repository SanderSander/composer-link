<?php

declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Copyright (c) 2021-2024 Sander Visser <themastersleader@hotmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 *
 * @link https://github.com/SanderSander/composer-link
 */

namespace ComposerLink\Actions;

use Composer\Repository\RepositoryManager;
use ComposerLink\LinkedPackage;
use ComposerLink\LinkManager;
use ComposerLink\Repository\Repository;

/**
 * Links all packages that aren't linked, and updates the state of the linked package with the orignal package.
 */
class LinkPackages
{
    public function __construct(
        protected readonly LinkManager $linkManager,
        protected readonly Repository $repository,
        protected readonly RepositoryManager $repositoryManager,
    ) {
    }

    public function execute(): void
    {
        foreach ($this->repository->all() as $package) {
            if (!$this->linkManager->isLinked($package)) {
                $this->linkAndUpdate($package);
            }
        }

        $this->repository->persist();
    }

    /**
     * It can happen, when a package is updated that we need to update the state of the linked package.
     * We do this here, before we link the package back in.
     */
    private function linkAndUpdate(LinkedPackage $package): void
    {
        $oldOriginalPackage = $package->getOriginalPackage();
        if (!is_null($oldOriginalPackage)) {
            $newOriginalPackage = $this->repositoryManager
                ->getLocalRepository()
                ->findPackage($oldOriginalPackage->getName(), '*');
            $package->setOriginalPackage($newOriginalPackage);
            $this->repository->store($package);
        }

        $this->linkManager->linkPackage($package);
    }
}
