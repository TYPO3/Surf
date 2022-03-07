<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\Surf\DeprecationMessageFactory;

class DeprecationMessageFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function createGenericDeprecationWarningForTaskMessage(): void
    {
        $expectedMessage = sprintf(
            'The usage of %s is deprecated and will be removed in TYPO3 Surf Version %s',
            self::class,
            '4.0.0'
        );

        self::assertSame(
            $expectedMessage,
            DeprecationMessageFactory::createGenericDeprecationWarningForTask(self::class, '4.0.0')
        );
    }
}
