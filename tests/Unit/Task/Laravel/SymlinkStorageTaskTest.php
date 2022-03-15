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
use TYPO3\Surf\Task\Laravel\SymlinkStorageTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class SymlinkStorageTaskTest extends BaseTaskTest
{
    protected function createTask(): SymlinkStorageTask
    {
        return new SymlinkStorageTask();
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
        $this->assertCommandExecuted("cd '{$this->application->getReleasesPath()}/{$this->deployment->getReleaseIdentifier()}'");
        $this->assertCommandExecuted("{ [ -d '../../shared/storage' ] || mkdir -p '../../shared/storage' ; }");
        $this->assertCommandExecuted("ln -sf '../../shared/storage' '{$this->application->getReleasesPath()}/{$this->deployment->getReleaseIdentifier()}/storage'");
    }
}
