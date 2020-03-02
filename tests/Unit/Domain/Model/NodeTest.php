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
    public function isLocalhost()
    {
        $node = new Node('Node');
        $node->onLocalhost();
        $this->assertTrue($node->isLocalhost());
    }

    /**
     * @test
     */
    public function setPort()
    {
        $node = new Node('Node');
        $node->setPort(222);
        $this->assertEquals(222, $node->getPort());
    }

    /**
     * @test
     */
    public function setUsername()
    {
        $node = new Node('Node');
        $node->setUsername('username');
        $this->assertEquals('username', $node->getUsername());
    }
}
