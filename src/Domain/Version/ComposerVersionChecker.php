<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Domain\Version;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;

final class ComposerVersionChecker implements VersionCheckerInterface
{
    public function isSatisified(string $packageName, string $constraint): bool
    {
        try {
            return InstalledVersions::satisfies(new VersionParser(), $packageName, $constraint);
        } catch (\OutOfBoundsException $outOfBoundsException) {
            return false;
        }
    }
}
