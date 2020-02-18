<?php

namespace TYPO3\Surf;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

final class DeprecationMessageFactory
{
    /**
     * @param string $className
     * @param string $versionOfSurfToRemoveTask
     *
     * @return string
     */
    public static function createGenericDeprecationWarningForTask($className, $versionOfSurfToRemoveTask = '3.0.0')
    {
        return sprintf('The usage of %s is deprecated and will be removed in TYPO3 Surf Version %s', $className, $versionOfSurfToRemoveTask);
    }
}
