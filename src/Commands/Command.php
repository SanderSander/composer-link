<?php

namespace ComposerLink\Commands;

use Composer\Command\BaseCommand;
use ComposerLink\Plugin;

abstract class Command extends BaseCommand
{
    protected Plugin $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct(null);
    }
}
