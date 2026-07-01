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
use Composer\EventDispatcher\Event;
use Composer\EventDispatcher\EventDispatcher;
use Composer\Installer;
use Composer\IO\IOInterface;
use Composer\Package\BasePackage;
use Composer\Package\RootPackageInterface;

class InstallerFactory
{
    public function __construct(
        protected IOInterface $io,
        protected Composer $composer,
    ) {
    }

    public function create(): Installer
    {
        // Use an isolated dispatcher so script events (post-update-cmd etc.) don't
        // fire and trigger Composer's circular-call detection when composer-link runs
        // inside a script handler. Plugin events are forwarded to the global dispatcher
        // so other plugins (e.g. version-constraint plugins) remain active.
        $eventDispatcher = new EventDispatcher($this->composer, $this->io);

        // Prevent circular call to script handler 'post-update-cmd' by creating a new composer instance
        $eventDispatcher->setRunScripts(false);

        $globalDispatcher = $this->composer->getEventDispatcher();
        foreach ($this->pluginEvents() as $eventName) {
            $eventDispatcher->addListener(
                $eventName,
                static function (Event $event) use ($globalDispatcher, $eventName): void {
                    $globalDispatcher->dispatch($eventName, $event);
                },
                PHP_INT_MAX
            );
        }

        /** @var RootPackageInterface&BasePackage $package */
        $package = $this->composer->getPackage();

        return new Installer(
            $this->io,
            $this->composer->getConfig(),
            $package,
            $this->composer->getDownloadManager(),
            $this->composer->getRepositoryManager(),
            $this->composer->getLocker(),
            $this->composer->getInstallationManager(),
            $eventDispatcher,
            $this->composer->getAutoloadGenerator()
        );
    }

    /** @return string[] */
    private function pluginEvents(): array
    {
        return [
            'pre-pool-create',
            'pre-operations-exec',
        ];
    }
}
