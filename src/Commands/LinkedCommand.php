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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LinkedCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('linked');
        $this->setDescription('List all linked packages');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $linkedPackages = $this->plugin->getRepository()->all();
        if (count($linkedPackages) === 0) {
            $output->writeln('No packages are linked');

            return 0;
        }

        $longest = 0;
        foreach ($linkedPackages as $linkedPackage) {
            if (strlen($linkedPackage->getName()) > $longest) {
                $longest = strlen($linkedPackage->getName());
            }
        }

        foreach ($linkedPackages as $linkedPackage) {
            $output->writeln(sprintf(
                "%s\t%s",
                str_pad($linkedPackage->getName(), $longest),
                $linkedPackage->getPath()
            ));
        }

        return 0;
    }
}
