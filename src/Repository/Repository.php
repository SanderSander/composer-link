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

namespace ComposerLink\Repository;

use ComposerLink\Package\LinkedPackage;
use RuntimeException;

class Repository
{
    /**
     * @var array<int, LinkedPackage>
     */
    protected array $linkedPackages = [];

    /**
     * Extra paths defined in composer.json to auto link packages.
     *
     * @var non-empty-string[]
     */
    protected array $extraPaths = [];

    /**
     * Contains paths of unlinked packages, that are also defined in $extra.
     *
     * @var non-empty-string[]
     */
    protected array $unlinkedFromExtra = [];

    public function __construct(
        protected readonly StorageInterface $storage,
        protected readonly Transformer $transformer,
    ) {
        $this->load();
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
     * @param non-empty-string[] $paths
     */
    public function setExtraPaths(array $paths): void
    {
        $this->extraPaths = $paths;
    }

    public function getExtraPaths(): array
    {
        return $this->extraPaths;
    }

    /**
     * @return non-empty-string[]
     */
    public function getUnlinkedFromExtra(): array
    {
        return $this->unlinkedFromExtra;
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
            throw new RuntimeException('Linked package not found');
        }

        array_splice($this->linkedPackages, $index, 1);
    }

    public function persist(): void
    {
        $data = [
            'packages' => [],
            'extra_paths' => $this->extraPaths,
        ];
        foreach ($this->linkedPackages as $package) {
            $data['packages'][] = $this->transformer->export($package);
        }

        $this->storage->write($data);
    }

    private function load(): void
    {
        if (!$this->storage->hasData()) {
            return;
        }

        $data = $this->storage->read();

        $this->extraPaths = $data['extra_paths'] ?? [];
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
