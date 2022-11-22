<?php

namespace ComposerLink\Actions;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;

class InstallLinkedPackages
{
    protected Composer $composer;

    protected IOInterface $io;

    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function execute(): void
    {

        $originalComposer = new JsonFile('./composer.json');
        $lockFile = new JsonFile('./vendor/linked-composer.lock');
        $replaceComposer = new JsonFile('./vendor/linked-composer.json');
        $replaceComposer->write($originalComposer->read());


    }
}