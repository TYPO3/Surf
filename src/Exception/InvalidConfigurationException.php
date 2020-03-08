<?php
namespace TYPO3\Surf\Exception;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception as SurfException;

class InvalidConfigurationException extends SurfException
{
    public static function createNoApplicationConfigured(): self
    {
        return new static('No application configured for deployment', 1334652420);
    }

    public static function createNoNodesConfigured(): self
    {
        return new static('No nodes configured for application', 1334652427);
    }

    public static function createNoDeploymentNameGiven(): self
    {
        return new static('No deployment name given!', 1451865016);
    }
}
