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

use Composer\DependencyResolver\Request;
use Composer\Installer;
use Composer\Package\Link;
use Composer\Semver\Constraint\MatchAllConstraint;
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
            'with-dependencies',
            null,
            InputOption::VALUE_NEGATABLE,
            'Also install package dependencies',
            false
        );
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
        $withDependencies = boolval($input->getOption('with-dependencies'));
        $paths = $this->getPaths($input);

        foreach ($paths as $path) {
            $package = $this->getPackage($path, $output, $withDependencies);

            if (is_null($package)) {
                continue;
            }

            if ($onlyInstalled && is_null($package->getOriginalPackage())) {
                continue;
            }

            if ($package->getWithDependencies()) {
                // We add the linked package as in the "require" section and we add a path repository to the package
                // Then we run a simple `composer update` and let composer do its thing.
                $composer = $this->requireComposer();
                $rootPackage = $composer->getPackage();

                // Configure the root package, we need to adjust some settings needed to install packages from paths.
                $rootPackage->setMinimumStability('dev');
                $rootPackage->setPreferStable(true);

                $constraint = new MatchAllConstraint();
                //$constraint = new Constraint('=', 'linked-dev');
                $link = new Link($rootPackage->getName(), $package->getName(), $constraint, Link::TYPE_REQUIRE);
                $rootPackage->setRequires(array_merge($rootPackage->getRequires(),
                    [$package->getName() => $link],
                ));
                $rootPackage->setRepositories(array_merge($rootPackage->getRepositories(), [
                    'type' => 'path',
                    'url' => $path->getNormalizedPath(),
                ]));

                // Use the composer installer to install the linked packages and dependencies
                $installer = Installer::create($this->getIO(), $composer);
                $installer->setUpdate(true)
                    ->setInstall(true)
                    ->setWriteLock(false)
                    ->setUpdateAllowTransitiveDependencies(Request::UPDATE_ONLY_LISTED);
                $installer->run();
            }
            else {
                $this->plugin->getLinkManager()->linkPackage($package);
            }

            // Could be optimized, but for now we persist every package,
            // so we know what we have done when a package fails
            $this->plugin->getRepository()->persist();
        }

        return 0;
    }

    protected function getPackage(PathHelper $helper, OutputInterface $output, bool $withDependencies): ?LinkedPackage
    {
        $linkedPackage = $this->plugin->getPackageFactory()->fromPath($helper->getNormalizedPath(), $withDependencies);
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
