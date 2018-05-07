<?php

namespace TYPO3\Surf;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

final class ErrorMessageFactory
{

    /**
     * @return string
     */
    public static function createDeprecationWarningForCoreApiUsage()
    {
        return 'The usage of coreapi is deprecated. Please use typo3_console instead. Integration in TYPO3 Surf will be removed in Version 3.0.0';
    }

}
