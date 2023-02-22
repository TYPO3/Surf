<?php

declare(strict_types=1);

namespace TYPO3\Surf\Domain\Version;

interface VersionCheckerInterface
{
    public function isSatisified(string $packageName, string $constraint): bool;
}
