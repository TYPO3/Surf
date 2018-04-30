<?php
namespace TYPO3\Surf\Tests\Unit\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Application;

/**
 * Unit test for Application
 */
class ApplicationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * The directory for shared assets is by default 'shared'
     *
     * @test
     * @return void
     */
    public function getSharedDirectoryReturnsDefaultIfNoOptionsGiven()
    {
        $application = new Application('TestApplication');
        $this->assertEquals('shared', $application->getSharedDirectory());
    }

    /**
     * If option 'sharedDirectory' is configured we expect this to be returned
     * by getSharedDirectory
     *
     * @test
     * @return void
     */
    public function getSharedDirectoryReturnsContentOfOptionIfConfigured()
    {
        $application = new Application('TestApplication');
        $application->setOption('sharedDirectory', 'sharedAssets');
        $this->assertEquals('sharedAssets', $application->getSharedDirectory());
    }

    /**
     * Relative paths are not allowed as sharedDirectory
     * we expect an exception on relative Paths
     *
     * @test
     * @expectedException \TYPO3\Surf\Exception\InvalidConfigurationException
     * @return void
     */
    public function getSharedDirectoryThrowsExceptionOnRelativePaths()
    {
        $application = new Application('TestApplication');
        $application->setOption('sharedDirectory', '../sharedAssets');
        $application->getSharedDirectory();
    }
}

