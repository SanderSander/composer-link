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

use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Semver\Constraint\Constraint;
use ComposerLink\Package\LinkedCompletePackage;

class LinkedPackage
{
    public function __construct(
        protected readonly string $path,
        protected readonly LinkedCompletePackage $package,
        protected ?PackageInterface $originalPackage,
        protected readonly string $installationPath,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->package->getName();
    }

    public function getPackage(): LinkedCompletePackage
    {
        return $this->package;
    }

    public function getOriginalPackage(): ?PackageInterface
    {
        return $this->originalPackage;
    }

    public function getInstallationPath(): string
    {
        return $this->installationPath;
    }

    /**
     * Creates a Link to this package from the given root.
     */
    public function createLink(RootPackageInterface $root): Link
    {
        return new Link(
            $root->getName(),
            $this->getName(),
            new Constraint('=', 'dev-linked'),
            Link::TYPE_REQUIRE
        );
    }

    public function setOriginalPackage(?PackageInterface $package): void
    {
        $this->originalPackage = $package;
    }
}
