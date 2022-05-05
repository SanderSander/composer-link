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
    protected function configure(): void
    {
        $this->setName('link');
        $this->setDescription('Link a package to a local directory');
        $this->addArgument('path', InputArgument::REQUIRED, 'The path of the package');
        $this->addOption(
            'only-installed',
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
        $paths = $this->getPaths($input);

        foreach ($paths as $path) {
            $package = $this->getPackage($path, $output);

            if (is_null($package)) {
                continue;
            }

            if ($input->getOption('only-installed') === true && $package->getOriginalPackage() === null) {
                continue;
            }

            $this->plugin->getRepository()->store($package);
            $this->plugin->getLinkManager()->linkPackage($package);

            // Could be optimized, but for now we persist every package,
            // so we know what we have done when a package fails
            $this->plugin->getRepository()->persist();
        }

        return 0;
    }

    /**
     * @return PathHelper[]
     */
    protected function getPaths(InputInterface $input): array
    {
        $helper = new PathHelper($input->getArgument('path'));

        // When run in global we should transform path to absolute path
        if ($this->plugin->isGlobal()) {
            /** @var string $working */
            $working = $this->getApplication()->getInitialWorkingDirectory();
            $helper = $helper->toAbsolutePath($working);
        }

        return $helper->isWildCard() ? $helper->getPathsFromWildcard() : [$helper];
    }

    protected function getPackage(PathHelper $helper, OutputInterface $output): ?LinkedPackage
    {
        $linkedPackage = $this->plugin->getPackageFactory()->fromPath($helper->getNormalizedPath());
        $repository = $this->plugin->getRepository();

        if (!is_null($repository->findByPath($helper->getNormalizedPath()))) {
            $output->writeln(
                sprintf('<warning>Package in path "%s" already linked</warning>', $helper->getNormalizedPath())
            );

            return null;
        }

        $currentLinked = $repository->findByName($linkedPackage->getName());
        if (!is_null($currentLinked)) {
            $output->writeln(
                sprintf(
                    '<warning>Package "%s" already linked from path "%s"</warning>',
                    $linkedPackage->getName(),
                    $currentLinked->getPath()
                )
            );

            return null;
        }

        return $linkedPackage;
    }
}
