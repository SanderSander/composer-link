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

namespace ComposerLink\Repository;

use Composer\IO\IOInterface;
use ComposerLink\LinkedPackage;
use League\Flysystem\FilesystemOperator;

class Repository
{
    protected const FILE_NAME = 'linked-packages.json';

    /** @deprecated We should get rid of all dependencies */
    protected FilesystemOperator $filesystem;

    protected IOInterface $io;

    protected Transformer $transformer;

    /**
     * @var array<int, LinkedPackage>
     */
    protected array $linkedPackages = [];

    public function __construct(FilesystemOperator $filesystem, IOInterface $io, Transformer $transformer)
    {
        $this->filesystem = $filesystem;
        $this->io = $io;
        $this->transformer = $transformer;

        $this->loadFromJsonFile();
    }

    public function store(LinkedPackage $linkedPackage): void
    {
        $index = $this->findIndex($linkedPackage);

        if (is_null($index)) {
            $this->linkedPackages[] = clone $linkedPackage;
            return;
        }

        $this->linkedPackages[$index] = clone $linkedPackage;
    }

    /**
     * @return LinkedPackage[]
     */
    public function all(): array
    {
        $all = [];
        foreach ($this->linkedPackages as $package) {
            $all[] = clone $package;
        }

        return $all;
    }

    public function findByPath(string $path): ?LinkedPackage
    {
        foreach ($this->linkedPackages as $linkedPackage) {
            if ($linkedPackage->getPath() === $path) {
                return clone $linkedPackage;
            }
        }

        return null;
    }

    public function findByName(string $name): ?LinkedPackage
    {
        foreach ($this->linkedPackages as $linkedPackage) {
            if ($linkedPackage->getName() === $name) {
                return clone $linkedPackage;
            }
        }

        return null;
    }

    public function remove(LinkedPackage $linkedPackage): void
    {
        $index = $this->findIndex($linkedPackage);

        if (is_null($index)) {
            throw new \RuntimeException('Linked package not found');
        }

        array_splice($this->linkedPackages, $index, 1);
    }

    public function persist(): void
    {
        $this->io->debug("[ComposerLink]\tStoring linked repositories data into json file");

        $data = [
            'packages' => []
        ];
        foreach ($this->linkedPackages as $package) {
            $data['packages'][] = $this->transformer->export($package);
        }

        /** @var string $json */
        $json = json_encode($data);
        $this->filesystem->write(self::FILE_NAME, $json);
    }

    /**
     * Load all linked packages from the json file into memory
     */
    private function loadFromJsonFile(): void
    {
        if (!$this->filesystem->fileExists(self::FILE_NAME)) {
            return;
        }
        $data = json_decode($this->filesystem->read(self::FILE_NAME), true);

        foreach ($data['packages'] as $package) {
            $this->linkedPackages[] = $this->transformer->load($package);
        }
    }

    private function findIndex(LinkedPackage $package): ?int
    {
        foreach ($this->linkedPackages as $index => $linkedPackage) {
            if ($linkedPackage->getName() === $package->getName()) {
                return $index;
            }
        }

        return null;
    }
}
