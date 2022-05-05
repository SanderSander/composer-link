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

namespace ComposerLink\Commands;

use ComposerLink\LinkedPackage;
use ComposerLink\PathHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LinkCommand extends Command
{
    // TODO We need to add a flag, to skip packages that are not installed (For when a wildcard is used)
    protected function configure(): void
    {
        $this->setName('link');
        $this->setDescription('Link a package to a local directory');
        $this->addArgument('path', InputArgument::REQUIRED, 'The path of the package');
        $this->addOption(
            '--only-installed',
            null,
            InputOption::VALUE_NEGATABLE,
            'Link only installed packages',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = new PathHelper($input->getArgument('path'));

        // When run in global we should transform path to absolute path
        if ($this->plugin->isGlobal()) {
            /** @var string $working */
            $working = $this->getApplication()->getInitialWorkingDirectory();
            $helper = $helper->toAbsolutePath($working);
        }

        $paths = $helper->isWildCard() ? $helper->getPathsFromWildcard() : [$helper];
        // TODO add support for --only-installed
        foreach ($paths as $path) {
            $package = $this->getPackage($path);
            if (is_null($package)) {
                continue;
            }
            $this->plugin->getRepository()->store($package);
            $this->plugin->getRepository()->persist();
            $this->plugin->getLinkManager()->linkPackage($package);
        }

        return 0;
    }

    protected function getPackage(PathHelper $helper): ?LinkedPackage
    {
        $linkedPackage = $this->plugin->getPackageFactory()->fromPath($helper->getNormalizedPath());
        $repository = $this->plugin->getRepository();

        if (!is_null($repository->findByPath($helper->getNormalizedPath()))) {
            $this->getIO()->writeError(
                sprintf('Package in path "%s" already linked', $helper->getNormalizedPath())
            );

            return null;
        }

        $currentLinked = $repository->findByName($linkedPackage->getName());
        if (!is_null($currentLinked)) {
            $this->getIO()->writeError(
                sprintf(
                    'Package "%s" already linked from path "%s"',
                    $linkedPackage->getName(),
                    $currentLinked->getPath()
                )
            );

            return null;
        }

        return $linkedPackage;
    }
}
