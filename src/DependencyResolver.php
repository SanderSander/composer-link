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

namespace ComposerLink;

use Composer\Composer;
use Composer\DependencyResolver\DefaultPolicy;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Request;
use Composer\DependencyResolver\Solver;
use Composer\DependencyResolver\SolverProblemsException;
use Composer\Filter\PlatformRequirementFilter\PlatformRequirementFilterFactory;
use Composer\IO\IOInterface;
use Composer\Repository\PathRepository;
use Composer\Repository\RepositorySet;
use Composer\Repository\RootPackageRepository;
use Composer\Semver\Constraint\Constraint;
use Composer\Util\ProcessExecutor;

class DependencyResolver
{
    protected Composer $composer;

    protected IOInterface $io;

    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * @return OperationInterface[]
     */
    public function resolveForPackage(LinkedPackage $package): array
    {
        // TODO use configuration of original composer.json
        $repositorySet = new RepositorySet(
            'dev',
            [],
            [],
        );

        // TODO we can't use this
        $exexutor = new ProcessExecutor($this->io);
        $exexutor->enableAsync();

        // Fill repositories first the root package repository
        $repositorySet->addRepository(new RootPackageRepository($this->composer->getPackage()));

        // The locked repository, we don't do a full upgrade, so we want to keep as much as possible packages up to date
        $repositorySet->addRepository($this->composer->getLocker()->getLockedRepository(true));

        // No we add our custom path repositories, we  need to do this before we add the original repositories
        // otherwise we do not get precedence over the other repositories
        $repo = new PathRepository(['url' => $package->getPath()], $this->io, $this->composer->getConfig(), null, null, $exexutor);
        $repositorySet->addRepository($repo);

        // Add custom repositories defined in the composer file
        $repositories = $this->composer->getRepositoryManager()->getRepositories();
        foreach ($repositories as $repository) {
            $repositorySet->addRepository($repository);
        }

        // Make request for package, and fix all the currently existing packages
        $request = new Request();
        $request->requireName($package->getName(), new Constraint('=', 'dev-master'));
        foreach ($this->composer->getRepositoryManager()->getLocalRepository()->getPackages() as $localPackage) {
            $request->fixPackage($localPackage);
        }
        $policy = new DefaultPolicy(true);

        // Create pool and solve?
        $pool = $repositorySet->createPool($request, $this->io);
        $solver = new Solver($policy, $pool, $this->io);

        $operations = [];

        try {
            $transaction = $solver->solve($request, PlatformRequirementFilterFactory::ignoreAll());
            $operations = $transaction->getOperations();
        } catch (SolverProblemsException $exception) {
            $this->io->write($exception->getPrettyString($repositorySet, $request, $pool, true));
        }

        return $operations;
    }
}
