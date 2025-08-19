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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UnlinkCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('unlink');
        $this->setDescription('Unlink a linked package');
        $this->addArgument('path', InputArgument::REQUIRED, 'The path of the package');
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
        /** @var non-empty-string $pathArgument */
        $pathArgument = $input->getArgument('path');
        $paths = $this->getPaths($pathArgument);
        $manager = $this->plugin->getLinkManager();

        foreach ($paths as $path) {
            $repository = $this->plugin->getRepository();
            $linkedPackage = $repository->findByPath($path->getNormalizedPath());

            if ($linkedPackage === null) {
                continue;
            }

            $manager->remove($linkedPackage);
        }

        $manager->linkPackages(!(bool) $input->getOption('no-dev'));

        return 0;
    }
}
