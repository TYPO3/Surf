<?php
namespace TYPO3\Surf\Tests\Unit\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

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

