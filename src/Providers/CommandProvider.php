<?php

namespace ComposerLink\Providers;

use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider as ComposerCommandProvider;
use ComposerLink\Commands\LinkCommand;
use ComposerLink\Commands\LinkedCommand;
use ComposerLink\Commands\UnlinkCommand;
use ComposerLink\Plugin;

class CommandProvider implements ComposerCommandProvider
{
    protected IOInterface $io;

    protected Plugin $plugin;

    public function __construct(array $arguments)
    {
        $this->io = $arguments['io'];
        $this->plugin = $arguments['plugin'];
    }

    public function getCommands(): array
    {
        $this->io->debug("[ComposerLink]\tInitializing commands.");

        return [
            new LinkCommand($this->plugin),
            new UnlinkCommand($this->plugin),
            new LinkedCommand($this->plugin)
        ];
    }
}
