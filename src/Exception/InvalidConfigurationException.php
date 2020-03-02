<?php

namespace TYPO3\Surf\Exception;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception as SurfException;

/**
 * Invalid deployment configuration exception.
 */
class InvalidConfigurationException extends SurfException
{
    /**
     * @return InvalidConfigurationException
     */
    public static function createNoApplicationConfigured()
    {
        return new static('No application configured for deployment', 1334652420);
    }

    /**
     * @return InvalidConfigurationException
     */
    public static function createNoNodesConfigured()
    {
        return new static('No nodes configured for application', 1334652427);
    }
}
