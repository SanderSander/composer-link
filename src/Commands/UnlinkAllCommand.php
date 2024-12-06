<?php

declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Created by: Sander Visser <themastersleader@hotmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 *
 * @link https://github.com/SanderSander/composer-link
 */

namespace ComposerLink\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnlinkAllCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('unlink-all');
        $this->setDescription('Unlink all linked package');
        $this->addOption(
            'no-dev',
            null,
            InputOption::VALUE_NONE,
            'Disables installation of require-dev packages.',
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $manager = $this->plugin->getLinkManager();
        $repository = $this->plugin->getRepository();

        foreach ($repository->all() as $package) {
            $manager->remove($package);
        }

        $manager->linkPackages(!(bool) $input->getOption('no-dev'));

        return 0;
    }
}
