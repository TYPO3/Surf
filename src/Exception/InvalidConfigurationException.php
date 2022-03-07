<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Exception;

use TYPO3\Surf\Exception as SurfException;

final class InvalidConfigurationException extends SurfException
{
    public static function createNoApplicationConfigured(): self
    {
        return new self('No application configured for deployment', 1334652420);
    }

    public static function createNoNodesConfigured(): self
    {
        return new self('No nodes configured for application', 1334652427);
    }

    public static function createTypo3ConsoleScriptNotFound(string $class): self
    {
        return new self('TYPO3 Console script was not found. Make sure it is available in your project and you set the "scriptFileName" option correctly. Alternatively you can remove this task (' . $class . ') in your deployment configuration.', 1481489230);
    }

    public static function createNoDeploymentNameGiven(): self
    {
        return new static('No deployment name given!', 1451865016);
    }
}
