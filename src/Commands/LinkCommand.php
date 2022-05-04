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

use ComposerLink\PathHelper;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LinkCommand extends Command
{
    // TODO We need to add a flag, to skip packages that are not installed (For when a wildcard is used)
    protected function configure(): void
    {
        $this->setName('link');
        $this->setDescription('Link a package to a local directory');
        $this->addArgument('path', InputArgument::REQUIRED, 'The path of the package');
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
        foreach ($paths as $path) {
            $this->linkPackage($path);
        }

        return 0;
    }


    // TODO instead of throwing exception, we should show a warning and continue,
    //      this is needed when use wildcards so we can continue the process
    protected function linkPackage(PathHelper $helper): void
    {
        $linkedPackage = $this->plugin->getPackageFactory()->fromPath($helper->getNormalizedPath());

        if (!is_null($this->plugin->getRepository()->findByPath($helper->getNormalizedPath()))) {
            throw new InvalidArgumentException(
                sprintf('Package in path "%s" already linked', $helper->getNormalizedPath())
            );
        }

        $currentLinked = $this->plugin->getRepository()->findByName($linkedPackage->getName());
        if (!is_null($currentLinked)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Package "%s" already linked from path "%s"',
                    $linkedPackage->getName(),
                    $currentLinked->getPath()
                )
            );
        }

        $this->plugin->getRepository()->store($linkedPackage);
        $this->plugin->getRepository()->persist();
        $this->plugin->getLinkManager()->linkPackage($linkedPackage);
    }
}
