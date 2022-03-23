<?php

namespace ComposerLink\Commands;

use Composer\Composer;
use ComposerLink\Factories\LinkedPackageFactory;
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');

        /** @var Composer $composer */
        $composer = $this->getComposer(true);

        $factory = new LinkedPackageFactory(
            $composer->getInstallationManager(),
            $composer->getRepositoryManager()->getLocalRepository()
        );
        $linkedPackage = $factory->fromPath($path);

        $this->plugin->getRepository()->store($linkedPackage);
        $this->plugin->getRepository()->persist();

        $this->plugin->getLinkedPackagesManager()->linkPackage($linkedPackage);

        return 0;
    }
}
