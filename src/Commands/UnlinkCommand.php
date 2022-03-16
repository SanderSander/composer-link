<?php

namespace ComposerLink\Commands;

use ComposerLink\Factories\LinkedPackageFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UnlinkCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('unlink');
        $this->setDescription('Unlink a linked package');
        $this->addArgument('path', InputArgument::REQUIRED, 'The path of the package');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');

        $factory = new LinkedPackageFactory($this->getComposer()->getInstallationManager(), $this->getComposer()->getRepositoryManager()->getLocalRepository());
        $linkedPackage = $factory->fromPath($path);


        $repository = $this->plugin->getRepository();
        $linkedPackage = $repository->findByPath($linkedPackage->getPath());

        if ($linkedPackage === null) {
            $this->getIO()->warning(sprintf('No linked package found in path "%s"', $path));
            return 1;
        }

        $this->plugin->getLinkedPackagesManager()->unlinkPackage($linkedPackage);
        $this->plugin->getRepository()->remove($linkedPackage);
        $this->plugin->getRepository()->persist();

        return 0;
    }
}
