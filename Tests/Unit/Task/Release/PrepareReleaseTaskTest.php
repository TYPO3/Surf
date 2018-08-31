<?php

namespace TYPO3\Surf\Tests\Unit\Task\Release;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use TYPO3\Surf\Task\Release\ReleaseTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class PrepareReleaseTaskTest extends BaseTaskTest
{

    /**
     * @test
     */
    public function requiredReleaseHostOptionIsMissing()
    {
        $this->expectException(InvalidOptionsException::class);
        $this->task->execute($this->node, $this->application, $this->deployment);
    }

    /**
     * @test
     */
    public function requiredOptionsAreDefinedTaskSuccessfullyExecuted()
    {
        $options = [
            'releaseHost' => 'releaseHost',
            'releaseHostLogin' => 'releaseHostLogin',
            'changeLogUri' => 'changeLogUri',
            'releaseHostSitePath' => 'releaseHostSitePath',
            'version' => 'version',
            'productName' => 'productName',
        ];

        $this->task->execute($this->node, $this->application, $this->deployment, $options);
        $this->assertCommandExecuted('ssh releaseHostLogin@releaseHost "cd \"releaseHostSitePath\" ; ./flow release:release --product-name \"productName\" --version \"version\" --change-log-uri \"changeLogUri\""');
    }

    /**
     * @return ReleaseTask
     */
    protected function createTask()
    {
        return new ReleaseTask();
    }
}
