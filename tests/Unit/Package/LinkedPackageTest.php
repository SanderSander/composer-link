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

namespace Tests\Unit\Package;

use Composer\Package\CompletePackageInterface;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Repository\RepositoryInterface;
use Composer\Semver\Constraint\Constraint;
use ComposerLink\Package\LinkedPackage;
use PHPUnit\Framework\TestCase;

class LinkedPackageTest extends TestCase
{
    public function test_linked_package(): void
    {
        $package = self::createStub(CompletePackageInterface::class);
        $package->method('getName')->willReturn('test/package');
        $originalPackage = self::createStub(PackageInterface::class);

        $linkedPackage = new LinkedPackage(
            $package,
            '/test-path',
            '/test-install-path',
            $originalPackage,
        );

        static::assertSame('/test-install-path', $linkedPackage->getInstallationPath());
        static::assertSame('/test-path', $linkedPackage->getPath());
        static::assertSame('/test-path', $linkedPackage->getDistUrl());
        static::assertSame('dist', $linkedPackage->getInstallationSource());
        static::assertSame('path', $linkedPackage->getDistType());
        static::assertSame('stable', $linkedPackage->getStability());
        static::assertSame('dev-linked', $linkedPackage->getVersion());
        static::assertFalse($linkedPackage->isWithoutDependencies());

        static::assertSame($package, $linkedPackage->getLinkedPackage());
        static::assertSame($originalPackage, $linkedPackage->getOriginalPackage());
        static::assertSame('test/package', $linkedPackage->getName());

        $newOriginalPackage = $this->createMock(PackageInterface::class);
        $linkedPackage->setOriginalPackage($newOriginalPackage);
        static::assertSame($newOriginalPackage, $linkedPackage->getOriginalPackage());
    }

    public function test_requires(): void
    {
        $link = $this->createMock(Link::class);
        $package = self::createStub(CompletePackageInterface::class);
        $package->method('getRequires')->willReturn(['test' => $link]);
        $package->method('getDevRequires')->willReturn(['dev-test' => $link]);
        $package->method('getName')->willReturn('test/package');
        $originalPackage = self::createStub(PackageInterface::class);
        $originalPackage->method('getRequires')->willReturn(['orig-test' => $link]);
        $originalPackage->method('getDevRequires')->willReturn(['orig-dev-test' => $link]);
        $linkedPackage = new LinkedPackage(
            $package,
            '/test-path',
            '/test-install-path',
            null
        );

        // With dependencies and no original package
        static::assertSame(['test' => $link], $linkedPackage->getRequires());
        static::assertSame(['dev-test' => $link], $linkedPackage->getDevRequires());

        // Without dependencies and no original package
        $linkedPackage->setWithoutDependencies(true);
        static::assertSame([], $linkedPackage->getRequires());
        static::assertSame([], $linkedPackage->getDevRequires());

        // Without dependencies and original package
        $linkedPackage->setOriginalPackage($originalPackage);
        static::assertSame(['orig-test' => $link], $linkedPackage->getRequires());
        static::assertSame(['orig-dev-test' => $link], $linkedPackage->getDevRequires());

        // With dependencies and original package
        $linkedPackage->setWithoutDependencies(false);
        static::assertSame(['test' => $link], $linkedPackage->getRequires());
        static::assertSame(['dev-test' => $link], $linkedPackage->getDevRequires());

        $root = $this->createMock(RootPackageInterface::class);
        $root->method('getName')->willReturn('root/package');
        $link = new Link('root/package', 'test/package', new Constraint('=', 'dev-linked'), Link::TYPE_REQUIRE);
        static::assertEquals($link, $linkedPackage->createLink($root));
    }

    public function test_decorated_methods(): void
    {
        $package = self::createStub(CompletePackageInterface::class);
        $linkedPackage = new LinkedPackage(
            $package,
            '/test-path',
            '/test-install-path',
            null
        );

        $linkedPackage->getScripts();
        $linkedPackage->setScripts([]);
        $linkedPackage->getRepositories();
        $linkedPackage->setRepositories([]);
        $linkedPackage->getLicense();
        $linkedPackage->setLicense([]);
        $linkedPackage->getKeywords();
        $linkedPackage->setKeywords([]);
        $linkedPackage->getDescription();
        $linkedPackage->setDescription('description');
        $linkedPackage->getHomepage();
        $linkedPackage->setHomepage('homepage');
        $linkedPackage->getAuthors();
        $linkedPackage->setAuthors([]);
        $linkedPackage->getSupport();
        $linkedPackage->setSupport([]);
        $linkedPackage->getFunding();
        $linkedPackage->setFunding([]);
        $linkedPackage->isAbandoned();
        $linkedPackage->getReplacementPackage();
        $linkedPackage->setAbandoned(true);
        $linkedPackage->getArchiveName();
        $linkedPackage->setArchiveName('name');
        $linkedPackage->getArchiveExcludes();
        $linkedPackage->setArchiveExcludes([]);
        $linkedPackage->getName();
        $linkedPackage->getPrettyName();
        $linkedPackage->getNames(false);
        $linkedPackage->setId(1);
        $linkedPackage->getId();
        $linkedPackage->isDev();
        $linkedPackage->getType();
        $linkedPackage->getTargetDir();
        $linkedPackage->getExtra();
        $linkedPackage->setInstallationSource('dist');
        $linkedPackage->getSourceType();
        $linkedPackage->getSourceUrl();
        $linkedPackage->getSourceUrls();
        $linkedPackage->getSourceReference();
        $linkedPackage->getSourceMirrors();
        $linkedPackage->setSourceMirrors([]);
        $linkedPackage->getDistUrls();
        $linkedPackage->getDistReference();
        $linkedPackage->getDistSha1Checksum();
        $linkedPackage->getDistMirrors();
        $linkedPackage->setDistMirrors([]);
        $linkedPackage->getPrettyVersion();
        $linkedPackage->getFullPrettyVersion(false, CompletePackageInterface::DISPLAY_DIST_REF);
        $linkedPackage->getReleaseDate();
        $linkedPackage->getConflicts();
        $linkedPackage->getProvides();
        $linkedPackage->getReplaces();
        $linkedPackage->getSuggests();
        $linkedPackage->getAutoload();
        $linkedPackage->getDevAutoload();
        $linkedPackage->getIncludePaths();
        $linkedPackage->getPhpExt();
        $repository = $this->createMock(RepositoryInterface::class);
        $linkedPackage->setRepository($repository);
        $linkedPackage->getRepository();
        $linkedPackage->getBinaries();
        $linkedPackage->getUniqueName();
        $linkedPackage->getNotificationUrl();
        $linkedPackage->__toString();
        $linkedPackage->getPrettyString();
        $linkedPackage->isDefaultBranch();
        $linkedPackage->getTransportOptions();
        $linkedPackage->setTransportOptions([]);
        $linkedPackage->setSourceReference('reference');
        $linkedPackage->setDistUrl('url');
        $linkedPackage->setDistType('type');
        $linkedPackage->setDistReference('reference');
        $linkedPackage->setSourceDistReferences('reference');

        static::expectNotToPerformAssertions();
    }
}
