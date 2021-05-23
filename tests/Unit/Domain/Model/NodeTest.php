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
use TYPO3\Surf\Exception\InvalidConfigurationException;

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

        self::assertEquals(222, $node->getPort());
    }

    /**
     * @test
     */
    public function setUsername(): void
    {
        $node = new Node('Node');
        $node->setUsername('username');

        self::assertEquals('username', $node->getUsername());
    }

    /**
     * The directory for shared assets is by default 'shared'
     *
     * @test
     * @throws InvalidConfigurationException
     */
    public function getSharedDirectoryReturnsDefaultIfNoOptionsGiven(): void
    {
        $node = new Node('Node');

        self::assertEquals('shared', $node->getSharedDirectory());
    }

    /**
     * If option 'sharedDirectory' is configured we expect this to be returned
     * by getSharedDirectory
     *
     * @test
     * @throws InvalidConfigurationException
     */
    public function getSharedDirectoryReturnsContentOfOptionIfConfigured(): void
    {
        $node = new Node('Node');
        $node->setOption('sharedDirectory', 'sharedAssets');

        self::assertEquals('sharedAssets', $node->getSharedDirectory());
    }

    /**
     * Relative paths are not allowed as sharedDirectory
     * we expect an exception on relative Paths
     *
     * @test
     */
    public function getSharedDirectoryThrowsExceptionOnRelativePaths(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $node = new Node('Node');
        $node->setOption('sharedDirectory', '../sharedAssets');
        $node->getSharedDirectory();
    }
}
