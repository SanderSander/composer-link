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

namespace ComposerLink\Exceptions;

use ComposerLink\Package\LinkedPackage;
use InvalidArgumentException;

class PackageAlreadyLinked extends InvalidArgumentException
{
    public function __construct(LinkedPackage $attempt, LinkedPackage $linked)
    {
        if ($attempt->getPath() !== $linked->getPath()) {
            parent::__construct(sprintf(
                'Package "%s" in "%s" already linked from path "%s"',
                $attempt->getName(),
                $attempt->getPath(),
                $linked->getPath()
            ));

            return;
        }

        parent::__construct(sprintf(
            'Package %s in path "%s" already linked"',
            $attempt->getName(),
            $attempt->getPath(),
        ));
    }
}
