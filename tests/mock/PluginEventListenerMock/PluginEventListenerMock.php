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

namespace Tests\Mock\PluginEventListener;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
final class PluginEventListenerMock implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'pre-pool-create' => 'onPrePoolCreate',
        ];
    }

    public function onPrePoolCreate(): void
    {
        $file = getcwd() . '/pre-pool-create-fired.txt';

        $count = file_exists($file)
            ? (int) file_get_contents($file)
            : 0;

        file_put_contents($file, (string) ($count + 1));
    }
}
