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
            InputOption::VALUE_NONE,
            'Link only installed packages',
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var bool $onlyInstalled */
        $onlyInstalled = $input->getOption('only-installed');
        $paths = $this->getPaths($input);

        foreach ($paths as $path) {
            $package = $this->getPackage($path, $output);

            if (is_null($package)) {
                continue;
            }

            if ($onlyInstalled && is_null($package->getOriginalPackage())) {
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
                    '<warning>Package "%s" in "%s" already linked from path "%s"</warning>',
                    $linkedPackage->getName(),
                    $linkedPackage->getPath(),
                    $currentLinked->getPath()
                )
            );

            return null;
        }

        return $linkedPackage;
    }
}
