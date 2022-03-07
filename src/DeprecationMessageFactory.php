<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf;

final class DeprecationMessageFactory
{
    public static function createGenericDeprecationWarningForTask(string $className, string $versionOfSurfToRemoveTask = '3.0.0'): string
    {
        return sprintf('The usage of %s is deprecated and will be removed in TYPO3 Surf Version %s', $className, $versionOfSurfToRemoveTask);
    }
}
