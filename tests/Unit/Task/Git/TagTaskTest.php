<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Task\Git;

use TYPO3\Surf\Task\Git\TagTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

class TagTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    protected function createTask(): TagTask
    {
        return new TagTask();
    }

    /**
     * @test
     */
    public function executeWithRequiredOptionsAndPushTagCreatesAndPushesTag(): void
    {
        $options = [
            'tagName' => 'release-{releaseIdentifier}',
            'description' => 'Release {releaseIdentifier} - by Surf.',
            'pushTag' => true
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('git tag -f -a -m \'Release ' . $this->deployment->getReleaseIdentifier() . ' - by Surf.\' \'release-' . $this->deployment->getReleaseIdentifier() . '\'');
        $this->assertCommandExecuted('git push \'origin\' \'release-' . $this->deployment->getReleaseIdentifier() . '\'');
    }

    /**
     * @test
     */
    public function executeWithRequiredOptionsAndRecurseIntoSubmodulesCreatesTagOnRootAndSubmodules(): void
    {
        $options = [
            'tagName' => 'release-{releaseIdentifier}',
            'description' => 'Release {releaseIdentifier} - by Surf.',
            'recurseIntoSubmodules' => true
        ];
        $this->task->execute($this->node, $this->application, $this->deployment, $options);

        $this->assertCommandExecuted('git tag -f -a -m \'Release ' . $this->deployment->getReleaseIdentifier() . ' - by Surf.\' \'release-' . $this->deployment->getReleaseIdentifier() . '\'');
        $this->assertCommandExecuted("git submodule foreach 'git tag -f -a -m '\\''Release {$this->deployment->getReleaseIdentifier()} - by Surf.'\\'' '\\''release-{$this->deployment->getReleaseIdentifier()}'\\'''");
    }
}
