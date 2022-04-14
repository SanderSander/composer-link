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

namespace ComposerLink\Actions;

use Composer\Repository\RepositoryManager;
use ComposerLink\LinkManager;
use ComposerLink\Repository\Repository;

class LinkPackages
{
    protected RepositoryManager $repositoryManager;

    protected Repository $repository;

    protected LinkManager $linkManager;

    public function __construct(
        LinkManager $linkManager,
        Repository $repository,
        RepositoryManager $repositoryManager
    ) {
        $this->linkManager = $linkManager;
        $this->repository = $repository;
        $this->repositoryManager = $repositoryManager;
    }

    public function execute(): void
    {
        foreach ($this->repository->all() as $linkedPackage) {
            if (!$this->linkManager->isLinked($linkedPackage)) {
                // Package is updated, so we need to link the newer original package
                $oldOriginalPackage = $linkedPackage->getOriginalPackage();
                if (!is_null($oldOriginalPackage)) {
                    $newOriginalPackage = $this->repositoryManager
                        ->getLocalRepository()
                        ->findPackage($oldOriginalPackage->getName(), '*');
                    $linkedPackage->setOriginalPackage($newOriginalPackage);
                    $this->repository->store($linkedPackage);
                }

                $this->linkManager->linkPackage($linkedPackage);
            }
        }

        $this->repository->persist();
    }
}
