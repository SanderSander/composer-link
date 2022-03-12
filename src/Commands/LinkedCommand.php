<?php

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('The following packages are linked:');
        $linkedPackages = $this->plugin->getRepository()->all();

        if (empty($linkedPackages)) {
            $output->writeln('No packages are linked');
        }

        foreach ($linkedPackages as $linkedPackage) {
            $output->writeln($linkedPackage->getPath());
        }

        return 0;
    }
}
