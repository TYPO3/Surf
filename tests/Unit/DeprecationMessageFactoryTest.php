<?php

namespace TYPO3\Surf\Tests\Unit;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use TYPO3\Surf\DeprecationMessageFactory;

class DeprecationMessageFactoryTest extends TestCase
{

    /**
     * @test
     */
    public function createGenericDeprecationWarningForTaskMessage()
    {
        $expectedMessage = sprintf('The usage of %s is deprecated and will be removed in TYPO3 Surf Version %s', __CLASS__, '4.0.0');
        $this->assertEquals($expectedMessage, DeprecationMessageFactory::createGenericDeprecationWarningForTask(__CLASS__, '4.0.0'));
    }
}
