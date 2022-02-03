<?php

namespace TYPO3\Surf\Tests\Unit\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use TYPO3\Surf\Domain\Model\Node;

class NodeTest extends TestCase
{
    /**
     * @test
     */
    public function isLocalhost(): void
    {
        $node = new Node('Node');
        $node->onLocalhost();

        self::assertTrue($node->isLocalhost());
    }

    /**
     * @test
     */
    public function setPort(): void
    {
        $node = new Node('Node');
        $node->setPort(222);

        self::assertSame(222, $node->getPort());
    }

    /**
     * @test
     */
    public function setUsername(): void
    {
        $node = new Node('Node');
        $node->setUsername('username');

        self::assertSame('username', $node->getUsername());
    }
}
