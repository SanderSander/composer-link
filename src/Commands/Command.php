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

use Composer\Command\BaseCommand;
use ComposerLink\PathHelper;
use ComposerLink\Plugin;

abstract class Command extends BaseCommand
{
    public function __construct(
        protected readonly Plugin $plugin,
    ) {
        parent::__construct();
    }

    /**
     * @param non-empty-string $path
     *
     * @return PathHelper[]
     */
    protected function getPaths(string $path): array
    {
        $helper = new PathHelper($path);

        // When run in global, we should transform the path to an absolute path
        if ($this->plugin->isGlobal()) {
            /** @var string $working */
            $working = $this->getApplication()->getInitialWorkingDirectory();
            $helper = $helper->toAbsolutePath($working);
        }

        return $helper->isWildCard() ? $helper->getPathsFromWildcard() : [$helper];
    }
}
