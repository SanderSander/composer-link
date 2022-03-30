<?php declare(strict_types=1);

/*
 * This file is part of the composer-link plugin.
 *
 * Copyright (c) 2021-2022 Sander Visser <themastersleader@hotmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 *
 * @link https://github.com/SanderSander/composer-link
 */

namespace ComposerLink;

use Composer\IO\IOInterface;
use League\Flysystem\FilesystemOperator;

class LinkedPackagesRepository
{
    protected const FILE_NAME = 'link.dat';

    protected FilesystemOperator $filesystem;

    protected IOInterface $io;

    /**
     * @var array<int, LinkedPackage>
     */
    protected array $linkedPackages = [];

    public function __construct(FilesystemOperator $filesystem, IOInterface $io)
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

    public function findByName(string $name): ?LinkedPackage
    {
        foreach ($this->linkedPackages as $linkedPackage) {
            if ($linkedPackage->getName() === $name) {
                return $linkedPackage;
            }
        }

        return null;
    }

    public function remove(LinkedPackage $linkedPackage): void
    {
        $index = array_search($linkedPackage, $this->linkedPackages, true);

        if ($index === false) {
            throw new \RuntimeException('Linked package not found');
        }

        array_splice($this->linkedPackages, $index, 1);
    }

    public function persist(): void
    {
        $this->io->debug("[ComposerLink]\tStoring linked repositories data into json file");
        // TODO use json
        $this->filesystem->write(self::FILE_NAME, serialize($this->linkedPackages));
    }

    /**
     * Load all linked packages from the json file into memory
     */
    private function loadFromJsonFile(): void
    {
        if (!$this->filesystem->fileExists(self::FILE_NAME)) {
            return;
        }

        // TODO use json
        $this->linkedPackages = unserialize($this->filesystem->read(self::FILE_NAME));
    }
}
