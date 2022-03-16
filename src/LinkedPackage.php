<?php

namespace ComposerLink;

use Composer\Package\CompletePackage;
use Composer\Package\PackageInterface;

class LinkedPackage
{
    protected string $path;

    protected CompletePackage $package;

    protected PackageInterface $originalPackage;

    protected string $installationPath;

    public function __construct(
        string $path,
        CompletePackage $package,
        PackageInterface $originalPackage,
        string $installationPath
    ) {
        $this->path = $path;
        $this->package = $package;
        $this->originalPackage = $originalPackage;
        $this->installationPath = $installationPath;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getPackage(): CompletePackage
    {
        return $this->package;
    }

    public function getOriginalPackage(): PackageInterface
    {
        return $this->originalPackage;
    }

    public function getInstallationPath(): string
    {
        return $this->installationPath;
    }
}
