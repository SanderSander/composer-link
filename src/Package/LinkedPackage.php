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
use Composer\Package\CompletePackageInterface;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\RepositoryInterface;
use Composer\Semver\Constraint\Constraint;
use Composer\Semver\Constraint\MultiConstraint;
use Composer\Semver\VersionParser;
use DateTimeInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class LinkedPackage extends BasePackage implements CompletePackageInterface
{
    protected bool $withoutDependencies = false;

    /**
     * @param non-empty-string $path
     */
    public function __construct(
        protected CompletePackageInterface $linkedPackage,
        protected string $path,
        protected string $installationPath,  // What's this?
        protected ?PackageInterface $original,          // Explain, it's the original package and not the linked package
    ) {
        parent::__construct($this->linkedPackage->getName());
    }

    /**
     * Creates a Link to this package from the given root.
     */
    public function createLink(RootPackageInterface $root, ?string $lockedVersion = null): Link
    {
        return new Link(
            $root->getName(),
            $this->getName(),
            new Constraint('=', 'dev-linked'),
            Link::TYPE_REQUIRE
        );
    }

    public function getOriginalPackage(): ?PackageInterface
    {
        return $this->original;
    }

    public function setOriginalPackage(?PackageInterface $package): void
    {
        $this->original = $package;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getInstallationPath(): string
    {
        return $this->installationPath;
    }

    public function setWithoutDependencies(bool $withoutDependencies): void
    {
        $this->withoutDependencies = $withoutDependencies;
    }

    public function isWithoutDependencies(): bool
    {
        return $this->withoutDependencies;
    }

    public function getRequires(): array
    {
        if ($this->withoutDependencies) {
            return $this->original?->getRequires() ?? [];
        }

        return $this->linkedPackage->getRequires();
    }

    public function getDevRequires(): array
    {
        if ($this->withoutDependencies) {
            return $this->original?->getDevRequires() ?? [];
        }

        return $this->linkedPackage->getDevRequires();
    }

    public function getLinkedPackage(): CompletePackageInterface
    {
        return $this->linkedPackage;
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
        // TODO I think the inline alias require
        return 'stable';
    }

    public function getVersion(): string
    {
        //return $this->original?->getVersion() ?? 'dev-linked';
        return 'dev-linked';
    }

    public function getReplaces(): array
    {
        return $this->linkedPackage->getReplaces();
    }


    //
    // Decorated functions, move altered function above this line
    //

    public function getScripts(): array
    {
        return $this->linkedPackage->getScripts();
    }

    public function setScripts(array $scripts): void
    {
        $this->linkedPackage->setScripts($scripts);
    }

    public function getRepositories(): array
    {
        return $this->linkedPackage->getRepositories();
    }

    public function setRepositories(array $repositories): void
    {
        $this->linkedPackage->setRepositories($repositories);
    }

    public function getLicense(): array
    {
        return $this->linkedPackage->getLicense();
    }

    public function setLicense(array $license): void
    {
        $this->linkedPackage->setLicense($license);
    }

    public function getKeywords(): array
    {
        return $this->linkedPackage->getKeywords();
    }

    public function setKeywords(array $keywords): void
    {
        $this->linkedPackage->setKeywords($keywords);
    }

    public function getDescription(): ?string
    {
        return $this->linkedPackage->getDescription();
    }

    public function setDescription(string $description): void
    {
        $this->linkedPackage->setDescription($description);
    }

    public function getHomepage(): ?string
    {
        return $this->linkedPackage->getHomepage();
    }

    public function setHomepage(string $homepage): void
    {
        $this->linkedPackage->setHomepage($homepage);
    }

    public function getAuthors(): array
    {
        return $this->linkedPackage->getAuthors();
    }

    public function setAuthors(array $authors): void
    {
        $this->linkedPackage->setAuthors($authors);
    }

    public function getSupport(): array
    {
        return $this->linkedPackage->getSupport();
    }

    public function setSupport(array $support): void
    {
        $this->linkedPackage->setSupport($support);
    }

    public function getFunding(): array
    {
        return $this->linkedPackage->getFunding();
    }

    public function setFunding(array $funding): void
    {
        $this->linkedPackage->setFunding($funding);
    }

    public function isAbandoned(): bool
    {
        return $this->linkedPackage->isAbandoned();
    }

    public function getReplacementPackage(): ?string
    {
        return $this->linkedPackage->getReplacementPackage();
    }

    public function setAbandoned($abandoned): void
    {
        $this->linkedPackage->setAbandoned($abandoned);
    }

    public function getArchiveName(): ?string
    {
        return $this->linkedPackage->getArchiveName();
    }

    public function setArchiveName(string $name): void
    {
        $this->linkedPackage->setArchiveName($name);
    }

    public function getArchiveExcludes(): array
    {
        return $this->linkedPackage->getArchiveExcludes();
    }

    public function setArchiveExcludes(array $excludes): void
    {
        $this->linkedPackage->setArchiveExcludes($excludes);
    }

    public function getName(): string
    {
        return $this->linkedPackage->getName();
    }

    public function getPrettyName(): string
    {
        return $this->linkedPackage->getPrettyName();
    }

    public function getNames($provides = true): array
    {
        return $this->linkedPackage->getNames($provides);
    }

    public function setId(int $id): void
    {
        $this->linkedPackage->setId($id);
    }

    public function getId(): int
    {
        return $this->linkedPackage->getId();
    }

    public function isDev(): bool
    {
        return $this->linkedPackage->isDev();
    }

    public function getType(): string
    {
        return $this->linkedPackage->getType();
    }

    public function getTargetDir(): ?string
    {
        return $this->linkedPackage->getTargetDir();
    }

    public function getExtra(): array
    {
        return $this->linkedPackage->getExtra();
    }

    public function setInstallationSource(?string $type): void
    {
        $this->linkedPackage->setInstallationSource($type);
    }

    public function getSourceType(): ?string
    {
        return $this->linkedPackage->getSourceType();
    }

    public function getSourceUrl(): ?string
    {
        return $this->linkedPackage->getSourceUrl();
    }

    public function getSourceUrls(): array
    {
        return $this->linkedPackage->getSourceUrls();
    }

    public function getSourceReference(): ?string
    {
        return $this->linkedPackage->getSourceReference();
    }

    public function getSourceMirrors(): ?array
    {
        return $this->linkedPackage->getSourceMirrors();
    }

    public function setSourceMirrors(?array $mirrors): void
    {
        $this->linkedPackage->setSourceMirrors($mirrors);
    }

    public function getDistUrls(): array
    {
        return $this->linkedPackage->getDistUrls();
    }

    public function getDistReference(): ?string
    {
        return $this->linkedPackage->getDistReference();
    }

    public function getDistSha1Checksum(): ?string
    {
        return $this->linkedPackage->getDistSha1Checksum();
    }

    public function getDistMirrors(): ?array
    {
        return $this->linkedPackage->getDistMirrors();
    }

    public function setDistMirrors(?array $mirrors): void
    {
        $this->linkedPackage->setDistMirrors($mirrors);
    }

    public function getPrettyVersion(): string
    {
        return $this->linkedPackage->getPrettyVersion();
    }

    public function getFullPrettyVersion(bool $truncate = true, int $displayMode = self::DISPLAY_SOURCE_REF_IF_DEV): string
    {
        return $this->linkedPackage->getFullPrettyVersion($truncate, $displayMode);
    }

    public function getReleaseDate(): ?DateTimeInterface
    {
        return $this->linkedPackage->getReleaseDate();
    }

    public function getConflicts(): array
    {
        return $this->linkedPackage->getConflicts();
    }

    public function getProvides(): array
    {
        return $this->linkedPackage->getProvides();
    }

    public function getSuggests(): array
    {
        return $this->linkedPackage->getSuggests();
    }

    public function getAutoload(): array
    {
        return $this->linkedPackage->getAutoload();
    }

    public function getDevAutoload(): array
    {
        return $this->linkedPackage->getDevAutoload();
    }

    public function getIncludePaths(): array
    {
        return $this->linkedPackage->getIncludePaths();
    }

    public function getPhpExt(): ?array
    {
        return $this->linkedPackage->getPhpExt();
    }

    public function setRepository(RepositoryInterface $repository): void
    {
        $this->linkedPackage->setRepository($repository);
    }

    public function getRepository(): ?RepositoryInterface
    {
        return $this->linkedPackage->getRepository();
    }

    public function getBinaries(): array
    {
        return $this->linkedPackage->getBinaries();
    }

    public function getUniqueName(): string
    {
        return $this->linkedPackage->getUniqueName();
    }

    public function getNotificationUrl(): ?string
    {
        return $this->linkedPackage->getNotificationUrl();
    }

    public function __toString(): string
    {
        return $this->linkedPackage->__toString();
    }

    public function getPrettyString(): string
    {
        return $this->linkedPackage->getPrettyString();
    }

    public function isDefaultBranch(): bool
    {
        return $this->linkedPackage->isDefaultBranch();
    }

    public function getTransportOptions(): array
    {
        return $this->linkedPackage->getTransportOptions();
    }

    public function setTransportOptions(array $options): void
    {
        $this->linkedPackage->setTransportOptions($options);
    }

    public function setSourceReference(?string $reference): void
    {
        $this->linkedPackage->setSourceReference($reference);
    }

    public function setDistUrl(?string $url): void
    {
        $this->linkedPackage->setDistUrl($url);
    }

    public function setDistType(?string $type): void
    {
        $this->linkedPackage->setDistType($type);
    }

    public function setDistReference(?string $reference): void
    {
        $this->linkedPackage->setDistReference($reference);
    }

    public function setSourceDistReferences(string $reference): void
    {
        $this->linkedPackage->setSourceDistReferences($reference);
    }
}
