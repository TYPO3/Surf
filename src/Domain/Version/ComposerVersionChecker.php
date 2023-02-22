<?php

declare(strict_types=1);

namespace TYPO3\Surf\Domain\Version;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;

final class ComposerVersionChecker implements VersionCheckerInterface
{

    public function isSatisified(string $packageName, string $constraint): bool
    {
        return InstalledVersions::satisfies(new VersionParser(), $packageName, $constraint);
    }
}
