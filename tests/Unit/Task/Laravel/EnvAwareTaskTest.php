<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Laravel;

use TYPO3\Surf\Application\Laravel;
use TYPO3\Surf\Task\Laravel\EnvAwareTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class EnvAwareTaskTest extends BaseTaskTest
{
    protected function createTask(): EnvAwareTask
    {
        return new EnvAwareTask();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->application = new Laravel('TestApplication');
        $this->application->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function executeWithoutArgumentsExecutesViewCacheWithoutArguments(): void
    {
        $this->task->execute($this->node, $this->application, $this->deployment);
        $this->assertCommandExecuted("test -f {$this->application->getSharedPath()}/.env");
        $this->assertCommandExecuted("cp '{$this->application->getSharedPath()}/.env' '{$this->application->getReleasesPath()}/{$this->deployment->getReleaseIdentifier()}/.env'");
    }
}
