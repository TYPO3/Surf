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
 * An exception during task execution
 *
 * Something went wrong or an assertion during task execution was not successful.
 */
class TaskExecutionException extends SurfException
{
}
