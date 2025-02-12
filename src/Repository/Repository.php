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
     * @var array<string, LinkedPackage>
     */
    protected array $linkedFromExtra = [];

    /**
     * Contains package names that were unlinked manually by the user,
     * but are defined in extra section of composer.json.
     *
     * @var array<string, LinkedPackage>
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
        // Package is defined in the extra section of composer.json
        if (isset($this->unlinkedFromExtra[$linkedPackage->getName()])) {
            unset($this->unlinkedFromExtra[$linkedPackage->getName()]);
            return;
        }

        $index = $this->findIndex($linkedPackage);

        if (is_null($index)) {
            $this->linkedPackages[] = clone $linkedPackage;

            return;
        }

        $this->linkedPackages[$index] = clone $linkedPackage;
    }

    public function hasUnlinkedFromExtra(LinkedPackage $linkedPackage): bool
    {
        return isset($this->unlinkedFromExtra[$linkedPackage->getName()]);
    }

    public function addLinkedFromExtra(LinkedPackage $linkedPackage): void
    {
        $this->linkedFromExtra[$linkedPackage->getName()] = $linkedPackage;
    }

    public function addUnlinkedFromExtra(LinkedPackage $package): void
    {
        $this->unlinkedFromExtra[$package->getName()] = $package;
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
        // Package was linked from extra, so remove the unlinked entry
        // This is need so that the package isn't linked automatically again
        if (isset($this->linkedFromExtra[$linkedPackage->getName()])) {
            $this->unlinkedFromExtra[$linkedPackage->getName()] = $this->linkedFromExtra[$linkedPackage->getName()];
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
            'linked_from_extra' => [],
            'unlinked_from_extra' => [],
        ];

        foreach ($this->linkedPackages as $package) {
            $data['packages'][] = $this->transformer->export($package);
        }
        foreach ($this->linkedFromExtra as $package) {
            $data['linked_from_extra'][] = $this->transformer->export($package);
        }
        foreach ($this->unlinkedFromExtra as $package) {
            $data['unlinked_from_extra'][] = $this->transformer->export($package);
        }

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
        if (isset($data['linked_from_extra'])) {
            foreach ($data['linked_from_extra'] as $record) {
                $package = $this->transformer->load($record);
                $this->linkedFromExtra[$package->getName()] = $package;
            }
        }
        if (isset($data['unlinked_from_extra'])) {
            foreach ($data['unlinked_from_extra'] as $record) {
                $package = $this->transformer->load($record);
                $this->unlinkedFromExtra[$package->getName()] = $package;
            }
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
