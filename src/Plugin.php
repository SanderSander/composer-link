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

namespace ComposerLink;

use Composer\Composer;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem as ComposerFileSystem;
use ComposerLink\Package\LinkedPackageFactory;
use ComposerLink\Repository\Repository;
use ComposerLink\Repository\RepositoryFactory;
use RuntimeException;

class Plugin implements PluginInterface, Capable, EventSubscriberInterface
{
    protected ?Repository $repository = null;

    protected ComposerFileSystem $filesystem;
    protected ?LinkedPackageFactory $packageFactory = null;

    protected Composer $composer;

    protected ?LinkManager $linkManager = null;

    protected RepositoryFactory $repositoryFactory;

    protected LinkManagerFactory $linkManagerFactory;

    protected bool $isUpgrading = false;

    public function __construct(
        ?ComposerFileSystem $filesystem = null,
        ?RepositoryFactory $repositoryFactory = null,
        ?LinkManagerFactory $linkManagerFactory = null,
    ) {
        $this->filesystem = $filesystem ?? new ComposerFileSystem();
        $this->repositoryFactory = $repositoryFactory ?? new RepositoryFactory();
        $this->linkManagerFactory = $linkManagerFactory ?? new LinkManagerFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function deactivate(Composer $composer, IOInterface $io): void
    {
        $io->debug('[ComposerLink] Plugin is deactivated.');
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(Composer $composer, IOInterface $io): void
    {
        $io->debug('[ComposerLink] Plugin uninstalling');
    }

    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $io->debug('[ComposerLink] Plugin activating');

        $this->composer = $composer;

        if ($this->detectUpgradeAndReset()) {
            $io->debug('[ComposerLink] Plugin just upgraded skipping initialization.');
            $this->isUpgrading = true;

            return;
        }

        $this->repository = $this->initializeRepository();
        $this->packageFactory = $this->initializeLinkedPackageFactory();
        $this->linkManager = $this->initializeLinkManager($io);
    }

    /**
     * This is a bit of a hack to detect upgrading.
     */
    protected function detectUpgradeAndReset(): bool
    {
        /** @var Package $thisPackage */
        $thisPackage = $this->composer
            ->getRepositoryManager()
            ->getLocalRepository()
            ->findPackage('sandersander/composer-link', '*');

        $extra = $thisPackage->getExtra();
        $upgrading = isset($extra['upgrading']);
        if ($upgrading) {
            unset($extra['upgrading']);
            $thisPackage->setExtra($extra);
        }

        return $upgrading;
    }

    protected function initializeRepository(): Repository
    {
        $storageFile = $this->composer->getConfig()
                ->get('vendor-dir') . DIRECTORY_SEPARATOR . 'linked-packages.json';

        return $this->repositoryFactory->create($storageFile);
    }

    protected function initializeLinkedPackageFactory(): LinkedPackageFactory
    {
        return new LinkedPackageFactory(
            $this->composer->getInstallationManager(),
            $this->composer->getRepositoryManager()->getLocalRepository()
        );
    }

    protected function initializeLinkManager(IOInterface $io): LinkManager
    {
        return $this->linkManagerFactory->create(
            $this->getRepository(),
            new InstallerFactory($io, $this->composer),
            $io,
            $this->composer
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_UPDATE_CMD => [
                ['postUpdate'],
            ],
            ScriptEvents::POST_INSTALL_CMD => [
                ['postInstall'],
            ],
            PackageEvents::PRE_PACKAGE_UPDATE => [
                ['prePackageUpdate'],
            ],
        ];
    }

    /**
     * If this package is upgraded, only the Plugin class is run through eval() with a renamed class name and constructed again
     * Since php doesn't have a way to remove defined classes in memory we have to solve this in a different way.
     *
     * For the moment we just set a flag in the extra section of this plugin
     */
    public function prePackageUpdate(PackageEvent $event): void
    {
        /** @var UpdateOperation $operation */
        $operation = $event->getOperation();
        /** @var Package $target */
        $target = $operation->getTargetPackage();
        if ($target->getName() === 'sandersander/composer-link') {
            $event->getIO()->debug('[ComposerLink] Plugin upgrade detected.');
            $target->setExtra(array_merge($target->getExtra(), ['upgrading' => true]));
        }
    }

    public function postInstall(Event $event): void
    {
        $linkManager = $this->getLinkManager();
        if ($linkManager->hasLinkedPackages()) {
            $linkManager->linkPackages($event->isDevMode());
        }
    }

    public function postUpdate(Event $event): void
    {
        // We just upgraded, so we do nothing
        if ($this->isUpgrading) {
            $event->getIO()->debug('[ComposerLink] Plugin upgrade detected, skipping post-update-cmd event.');
            $event->getIO()->warning('<warning>Composer link has just been updated, run `composer install` again to link packages</warning>');
            return;
        }

        $linkManager = $this->getLinkManager();
        $repository = $this->getRepository();

        if ($linkManager->hasLinkedPackages()) {
            $localRepository = $this->composer->getRepositoryManager()->getLocalRepository();
            //  It can happen that a original package is updated,
            //  in those cases we need to update the state of the linked package by setting the original package
            foreach ($repository->all() as $package) {
                $original = $localRepository->findPackage($package->getName(), '*');
                $package->setOriginalPackage($original);
            }
            $repository->persist();

            $linkManager->linkPackages($event->isDevMode());
        }
    }

    public function getLinkManager(): LinkManager
    {
        if (is_null($this->linkManager)) {
            throw new RuntimeException('Plugin not activated');
        }

        return $this->linkManager;
    }

    public function getCapabilities(): array
    {
        return [
            ComposerCommandProvider::class => CommandProvider::class,
        ];
    }

    public function getRepository(): Repository
    {
        if (is_null($this->repository)) {
            throw new RuntimeException('Plugin not activated');
        }

        return $this->repository;
    }

    public function getPackageFactory(): LinkedPackageFactory
    {
        if (is_null($this->packageFactory)) {
            throw new RuntimeException('Plugin not activated');
        }

        return $this->packageFactory;
    }

    /**
     * Check if this plugin is running from global or local project.
     */
    public function isGlobal(): bool
    {
        return getcwd() === $this->composer->getConfig()->get('home');
    }
}
