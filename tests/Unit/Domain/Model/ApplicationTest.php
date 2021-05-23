<?php
namespace TYPO3\Surf\Tests\Unit\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * Unit test for Application
 * @deprecated Can be removed in Surf 4.0
 */
class ApplicationTest extends TestCase
{
    /**
     * The directory for shared assets is by default 'shared'
     *
     * @test
     */
    public function getSharedDirectoryReturnsDefaultIfNoOptionsGiven(): void
    {
        $application = new Application('TestApplication');

        self::assertEquals('shared', $application->getSharedDirectory());
    }

    /**
     * If option 'sharedDirectory' is configured we expect this to be returned
     * by getSharedDirectory
     *
     * @test
     */
    public function getSharedDirectoryReturnsContentOfOptionIfConfigured(): void
    {
        $application = new Application('TestApplication');
        $application->setOption('sharedDirectory', 'sharedAssets');

        self::assertEquals('sharedAssets', $application->getSharedDirectory());
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

        $application = new Application('TestApplication');
        $application->setOption('sharedDirectory', '../sharedAssets');
        $application->getSharedDirectory();
    }
}
