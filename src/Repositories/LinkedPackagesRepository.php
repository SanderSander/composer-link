<?php

namespace ComposerLink\Repositories;

use Composer\IO\IOInterface;
use ComposerLink\Entities\LinkedPackage;
use League\Flysystem\Filesystem;

class LinkedPackagesRepository
{
    protected const JSON_FILE_NAME = 'composer-link.json';

    protected Filesystem $filesystem;

    protected IOInterface $io;

    /**
     * @var LinkedPackage[]
     */
    protected array $linkedPackages = [];

    public function __construct(Filesystem $filesystem, IOInterface $io)
    {
        $this->filesystem = $filesystem;
        $this->io = $io;

        $this->loadFromJsonFile();
    }

    public function store(LinkedPackage $linkedPackage): void
    {
        $this->io->debug("[ComposerLink]\tStoring linked repository into memory");
        $this->linkedPackages[] = $linkedPackage;
    }

    /**
     * @return LinkedPackage[]
     */
    public function all(): array
    {
        return $this->linkedPackages;
    }

    public function findByPath(string $path): ?LinkedPackage
    {
        foreach ($this->linkedPackages as $linkedPackage) {
            if ($linkedPackage->getPath() === $path) {
                return $linkedPackage;
            }
        }

        return null;
    }

    public function remove(LinkedPackage $linkedPackage): void
    {
        $index = array_search($linkedPackage, $this->linkedPackages);

        if ($index === false) {
            return;
        }

        array_splice($this->linkedPackages, $index, 1);
    }

    public function persist(): void
    {
        $this->io->debug("[ComposerLink]\tStoring linked repositories data into json file");
        $this->filesystem->write(self::JSON_FILE_NAME, serialize($this->linkedPackages));
    }

    /**
     * Load all linked packages from the json file into memory
     */
    private function loadFromJsonFile(): void
    {
        if (!$this->filesystem->fileExists(self::JSON_FILE_NAME)) {
            return;
        }

        $this->linkedPackages = unserialize($this->filesystem->read(self::JSON_FILE_NAME));
    }
}
