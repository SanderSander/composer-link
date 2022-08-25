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
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\DependencyResolver\Request;
use Composer\DependencyResolver\Solver;
use Composer\DependencyResolver\SolverProblemsException;
use Composer\Filter\PlatformRequirementFilter\PlatformRequirementFilterFactory;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\PathRepository;
use Composer\Repository\RepositorySet;
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
     * @return PackageInterface[]
     */
    public function resolveForPackage(LinkedPackage $package): array
    {
        // ////////////////////////////////////////////////////
        $repositorySet = new RepositorySet(
            'dev',
            [],
            [],
        );

        $exexutor = new ProcessExecutor($this->io);
        $exexutor->enableAsync();

        // Fill repositories
        // $repositorySet->addRepository(new RootPackageRepository($this->composer->getPackage()));

        $repositories = $this->composer->getRepositoryManager()->getRepositories();
        foreach ($repositories as $repository) {
            $repositorySet->addRepository($repository);
        }

        $repo = new PathRepository(['url' => $package->getPath()], $this->io, $this->composer->getConfig(), null, null, $exexutor);
        $repositorySet->addRepository($repo);

        // Make request for package
        $request = new Request();
        $request->requireName($package->getName(), new Constraint('=', 'dev-master'));
        $policy = new DefaultPolicy(true);

        // Create pool and solve?
        $pool = $repositorySet->createPool($request, $this->io);
        $solver = new Solver($policy, $pool, $this->io);

        $installs = [];

        try {
            $transaction = $solver->solve($request, PlatformRequirementFilterFactory::ignoreAll());
            $operations = $transaction->getOperations();
            foreach ($operations as $operation) {
                if ($operation instanceof InstallOperation) {
                    $installs[] = $operation->getPackage();
                }
                if ($operation instanceof UpdateOperation) {
                    $updates = $operation->getTargetPackage();
                }
                if ($operation instanceof UninstallOperation) {
                    // TODO implement
                }
            }
        } catch (SolverProblemsException $exception) {
            $this->io->write($exception->getPrettyString($repositorySet, $request, $pool, true));
        }

        // //////////////////////////////////////////////
    }
}
