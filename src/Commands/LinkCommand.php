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

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LinkCommand extends Command
{
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
        $path = $input->getArgument('path');

        $linkedPackage = $this->plugin->getPackageFactory()->fromPath($path);

        if (!is_null($this->plugin->getRepository()->findByPath($path))) {
            throw new RuntimeException(sprintf('Package in path "%s" already linked', $path));
        }

        $currentLinked = $this->plugin->getRepository()->findByName($linkedPackage->getName());
        if (!is_null($currentLinked)) {
            throw new RuntimeException(
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

        return 0;
    }
}
