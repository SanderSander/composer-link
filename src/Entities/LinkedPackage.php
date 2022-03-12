<?php

namespace ComposerLink\Entities;

class LinkedPackage
{
    protected string $path;

    protected string $name;

    public function __construct(
        string $path,
        string $name
    ) {
        $this->path = $path;
        $this->name = $name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
