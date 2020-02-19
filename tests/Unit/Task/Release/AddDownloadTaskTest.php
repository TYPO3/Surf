<?php

namespace TYPO3\Surf\Tests\Unit\Task\Release;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Task\Release\AddDownloadTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class AddDownloadTaskTest extends BaseTaskTest
{

    /**
     * @test
     */
    public function requiredReleaseHostOptionIsMissing()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @test
     */
    public function requiredOptionsAreDefinedTaskSuccessfullyExecuted()
    {
        $options = [
            'releaseHost' => 'somevalue',
            'releaseHostLogin' => 'somevalue',
            'releaseHostSitePath' => 'somevalue',
            'version' => 'somevalue',
            'label' => 'somevalue',
            'downloadUriPattern' => 'somevalue',
            'productName' => 'somevalue',
            'files' => ['somevalue'],
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('ssh somevalue@somevalue "cd \"somevalue\" ; ./flow release:adddownload --product-name \"somevalue\" --version \"somevalue\" --label \"somevalue\" "somevalue,359249010a1d7f1f47ac8e5cbaaff40fb1a34070,somevalue""');
    }

    /**
     * @return AddDownloadTask
     */
    protected function createTask()
    {
        return new AddDownloadTask();
    }
}
