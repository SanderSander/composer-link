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

namespace ComposerLink\Package;

use Composer\Package\BasePackage;
use Composer\Package\CompletePackage;
use Composer\Package\CompletePackageInterface;
use Composer\Repository\RepositoryInterface;
use DateTimeInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class LinkedCompletePackage extends BasePackage implements CompletePackageInterface
{
    /**
     * @param non-empty-string $path
     */
    public function __construct(
        protected CompletePackage $package,
        protected string $path,
    ) {
        parent::__construct($this->package->getName());
    }

    /**
     * We always install from dist because we load the package from a path.
     */
    public function getInstallationSource(): ?string
    {
        return 'dist';
    }

    /**
     * Force loading from path.
     */
    public function getDistType(): ?string
    {
        return 'path';
    }

    /**
     * Return the path from where this package is linked.
     */
    public function getDistUrl(): ?string
    {
        return $this->path;
    }

    /**
     * We always return our own stability, this way we can link the package without considering minimal-stability settings.
     */
    public function getStability(): string
    {
        return 'stable';
    }

    public function getVersion(): string
    {
        return 'dev-linked';
    }

    //
    // Decorated functions, move altered function above this line
    //

    public function getScripts(): array
    {
        return $this->package->getScripts();
    }

    public function setScripts(array $scripts): void
    {
        $this->package->setScripts($scripts);
    }

    public function getRepositories(): array
    {
        return $this->package->getRepositories();
    }

    public function setRepositories(array $repositories): void
    {
        $this->package->setRepositories($repositories);
    }

    public function getLicense(): array
    {
        return $this->package->getLicense();
    }

    public function setLicense(array $license): void
    {
        $this->package->setLicense($license);
    }

    public function getKeywords(): array
    {
        return $this->package->getKeywords();
    }

    public function setKeywords(array $keywords): void
    {
        $this->package->setKeywords($keywords);
    }

    public function getDescription(): ?string
    {
        return $this->package->getDescription();
    }

    public function setDescription(string $description): void
    {
        $this->package->setDescription($description);
    }

    public function getHomepage(): ?string
    {
        return $this->package->getHomepage();
    }

    public function setHomepage(string $homepage): void
    {
        $this->package->setHomepage($homepage);
    }

    public function getAuthors(): array
    {
        return $this->package->getAuthors();
    }

    public function setAuthors(array $authors): void
    {
        $this->package->setAuthors($authors);
    }

    public function getSupport(): array
    {
        return $this->package->getSupport();
    }

    public function setSupport(array $support): void
    {
        $this->package->setSupport($support);
    }

    public function getFunding(): array
    {
        return $this->package->getFunding();
    }

    public function setFunding(array $funding): void
    {
        $this->package->setFunding($funding);
    }

    public function isAbandoned(): bool
    {
        return $this->package->isAbandoned();
    }

    public function getReplacementPackage(): ?string
    {
        return $this->package->getReplacementPackage();
    }

    public function setAbandoned($abandoned): void
    {
        $this->package->setAbandoned($abandoned);
    }

    public function getArchiveName(): ?string
    {
        return $this->package->getArchiveName();
    }

    public function setArchiveName(string $name): void
    {
        $this->package->setArchiveName($name);
    }

    public function getArchiveExcludes(): array
    {
        return $this->package->getArchiveExcludes();
    }

    public function setArchiveExcludes(array $excludes): void
    {
        $this->package->setArchiveExcludes($excludes);
    }

    public function getName(): string
    {
        return $this->package->getName();
    }

    public function getPrettyName(): string
    {
        return $this->package->getPrettyName();
    }

    public function getNames($provides = true): array
    {
        return $this->package->getNames($provides);
    }

    public function setId(int $id): void
    {
        $this->package->setId($id);
    }

    public function getId(): int
    {
        return $this->package->getId();
    }

    public function isDev(): bool
    {
        return $this->package->isDev();
    }

    public function getType(): string
    {
        return $this->package->getType();
    }

    public function getTargetDir(): ?string
    {
        return $this->package->getTargetDir();
    }

    public function getExtra(): array
    {
        return $this->package->getExtra();
    }

    public function setInstallationSource(?string $type): void
    {
        $this->package->setInstallationSource($type);
    }

    public function getSourceType(): ?string
    {
        return $this->package->getSourceType();
    }

    public function getSourceUrl(): ?string
    {
        return $this->package->getSourceUrl();
    }

    public function getSourceUrls(): array
    {
        return $this->package->getSourceUrls();
    }

    public function getSourceReference(): ?string
    {
        return $this->package->getSourceReference();
    }

    public function getSourceMirrors(): ?array
    {
        return $this->package->getSourceMirrors();
    }

    public function setSourceMirrors(?array $mirrors): void
    {
        $this->package->setSourceMirrors($mirrors);
    }

    public function getDistUrls(): array
    {
        return $this->package->getDistUrls();
    }

    public function getDistReference(): ?string
    {
        return $this->package->getDistReference();
    }

    public function getDistSha1Checksum(): ?string
    {
        return $this->package->getDistSha1Checksum();
    }

    public function getDistMirrors(): ?array
    {
        return $this->package->getDistMirrors();
    }

    public function setDistMirrors(?array $mirrors): void
    {
        $this->package->setDistMirrors($mirrors);
    }

    public function getPrettyVersion(): string
    {
        return $this->package->getPrettyVersion();
    }

    public function getFullPrettyVersion(bool $truncate = true, int $displayMode = self::DISPLAY_SOURCE_REF_IF_DEV): string
    {
        return $this->package->getFullPrettyVersion($truncate, $displayMode);
    }

    public function getReleaseDate(): ?DateTimeInterface
    {
        return $this->package->getReleaseDate();
    }

    public function getRequires(): array
    {
        return $this->package->getRequires();
    }

    public function getConflicts(): array
    {
        return $this->package->getConflicts();
    }

    public function getProvides(): array
    {
        return $this->package->getProvides();
    }

    public function getReplaces(): array
    {
        return $this->package->getReplaces();
    }

    public function getDevRequires(): array
    {
        return $this->package->getDevRequires();
    }

    public function getSuggests(): array
    {
        return $this->package->getSuggests();
    }

    public function getAutoload(): array
    {
        return $this->package->getAutoload();
    }

    public function getDevAutoload(): array
    {
        return $this->package->getDevAutoload();
    }

    public function getIncludePaths(): array
    {
        return $this->package->getIncludePaths();
    }

    public function getPhpExt(): ?array
    {
        return $this->package->getPhpExt();
    }

    public function setRepository(RepositoryInterface $repository): void
    {
        $this->package->setRepository($repository);
    }

    public function getRepository(): ?RepositoryInterface
    {
        return $this->package->getRepository();
    }

    public function getBinaries(): array
    {
        return $this->package->getBinaries();
    }

    public function getUniqueName(): string
    {
        return $this->package->getUniqueName();
    }

    public function getNotificationUrl(): ?string
    {
        return $this->package->getNotificationUrl();
    }

    public function __toString(): string
    {
        return $this->package->__toString();
    }

    public function getPrettyString(): string
    {
        return $this->package->getPrettyString();
    }

    public function isDefaultBranch(): bool
    {
        return $this->package->isDefaultBranch();
    }

    public function getTransportOptions(): array
    {
        return $this->package->getTransportOptions();
    }

    public function setTransportOptions(array $options): void
    {
        $this->package->setTransportOptions($options);
    }

    public function setSourceReference(?string $reference): void
    {
        $this->package->setSourceReference($reference);
    }

    public function setDistUrl(?string $url): void
    {
        $this->package->setDistUrl($url);
    }

    public function setDistType(?string $type): void
    {
        $this->package->setDistType($type);
    }

    public function setDistReference(?string $reference): void
    {
        $this->package->setDistReference($reference);
    }

    public function setSourceDistReferences(string $reference): void
    {
        $this->package->setSourceDistReferences($reference);
    }
}
