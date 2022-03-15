<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Neos\Flow;

use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Task\Neos\Flow\SymlinkConfigurationTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class SymlinkConfigurationTaskTest extends BaseTaskTest
{
    /**
     * @var Flow
     */
    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Flow('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    protected function createTask(): SymlinkConfigurationTask
    {
        return new SymlinkConfigurationTask();
    }

    /**
     * @test
     */
    public function executeWithFlowApplicationRespectsContext(): void
    {
        $this->application->setContext('Development');
        $this->task->execute($this->node, $this->application, $this->deployment, []);

        $this->assertCommandExecuted('if [ -d Development ]; then rm -Rf Development; fi');
        $this->assertCommandExecuted('mkdir -p ../../../shared/Configuration/Development');
        $this->assertCommandExecuted('ln -snf ../../../shared/Configuration/Development Development');
    }

    /**
     * @test
     */
    public function executeWithFlowApplicationRespectsSubContext(): void
    {
        $this->application->setContext('Production/Foo');
        $this->task->execute($this->node, $this->application, $this->deployment, []);

        $this->assertCommandExecuted('if [ -d Production/Foo ]; then rm -Rf Production/Foo; fi');
        $this->assertCommandExecuted('mkdir -p ../../../shared/Configuration/Production/Foo');
        $this->assertCommandExecuted('mkdir -p Production');
        $this->assertCommandExecuted('ln -snf ../../../../shared/Configuration/Production/Foo Production/Foo');
    }
}
