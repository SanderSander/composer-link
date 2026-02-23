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
     * @var LinkedPackage[]
     */
    protected array $extraPackages = [];

    /**
     * @var string[]
     */
    protected array $unlinkedExtra = [];

    public function __construct(
        protected readonly StorageInterface $storage,
        protected readonly Transformer $transformer,
        /** @var non-empty-string[] */
        protected readonly array $extra,
    ) {
        $this->load();
    }

    public function store(LinkedPackage $linkedPackage): void
    {
        if ($this->isFromExtra($linkedPackage)) {
            $index = array_search($linkedPackage->getName(), $this->unlinkedExtra, true);
            if ($index !== false) {
                unset($this->unlinkedExtra[$index]);
                $this->unlinkedExtra = array_values($this->unlinkedExtra);
            }

            return;
        }

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
        foreach ($this->extraPackages as $package) {
            if (in_array($package->getName(), $this->unlinkedExtra, true)) {
                continue;
            }
            $all[] = clone $package;
        }

        foreach ($this->linkedPackages as $package) {
            $all[] = clone $package;
        }

        return $all;
    }

    public function findByPath(string $path): ?LinkedPackage
    {
        foreach ($this->extraPackages as $extraPackage) {
            if ($extraPackage->getPath() === $path && !in_array($extraPackage->getName(), $this->unlinkedExtra, true)) {
                return clone $extraPackage;
            }
        }

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
        if ($this->isFromExtra($linkedPackage)) {
            if (in_array($linkedPackage->getName(), $this->unlinkedExtra, true)) {
                return;
            }
            $this->unlinkedExtra[] = $linkedPackage->getName();

            return;
        }

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
        ];
        foreach ($this->linkedPackages as $package) {
            $data['packages'][] = $this->transformer->export($package);
        }
        $data['unlinkedExtra'] = $this->unlinkedExtra;

        $this->storage->write($data);
    }

    private function load(): void
    {
        if (!$this->storage->hasData()) {
            return;
        }

        $data = $this->storage->read();

        foreach ($data['packages'] as $package) {
            $this->linkedPackages[] = $this->transformer->load($package);
        }

        // Load extra packages
        foreach ($this->extra as $extraPackage) {
            $this->extraPackages[] = $this->transformer->load(['path' => $extraPackage, 'withoutDependencies' => false]);
        }

        $this->unlinkedExtra = $data['unlinkedExtra'] ?? [];
    }

    private function isFromExtra(LinkedPackage $package): bool
    {
        foreach ($this->extraPackages as $extraPackage) {
            if ($extraPackage->getName() === $package->getName()) {
                return true;
            }
        }

        return false;
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
