<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit;

use Symfony\Component\HttpKernel\Kernel;
use TYPO3\Surf\Cli\Symfony\ConsoleKernel;

trait KernelAwareTrait
{
    /**
     * @var Kernel
     */
    protected static $kernel;

    public static function getKernel(): Kernel
    {
        if (static::$kernel === null) {
            $kernel = new ConsoleKernel('test', true);
            $kernel->boot();
            static::$kernel = $kernel;
        }
        return static::$kernel;
    }
}
