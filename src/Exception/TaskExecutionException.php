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

    /**
     * @param string $scriptUrl
     *
     * @return TaskExecutionException
     */
    public static function webOpcacheResetExecuteTaskDidNotReturnExpectedResult($scriptUrl)
    {
        return new static(sprintf('WebOpcacheResetExecuteTask at "%s" did not return expected result', $scriptUrl), 1471511860);
    }

    /**
     * @param string $scriptFilename
     *
     * @return TaskExecutionException
     */
    public static function webOpcacheResetCreateScriptTaskCouldNotWritFile($scriptFilename)
    {
        return new static(sprintf('Could not write file "%s"', $scriptFilename), 1421932414);
    }
}
