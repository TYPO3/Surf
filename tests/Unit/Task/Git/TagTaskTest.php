<?php
namespace TYPO3\Surf\Tests\Unit\Task\Git;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Task\Git\TagTask;
use TYPO3\Surf\Tests\Unit\Task\BaseTaskTest;

/**
 * Unit test for the TagTask
 */
class TagTaskTest extends BaseTaskTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->node->setDeploymentPath('/home/jdoe/app');
    }

    /**
     * @test
     */
    public function executeWithRequiredOptionsAndPushTagCreatesAndPushesTag()
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
    public function executeWithRequiredOptionsAndRecurseIntoSubmodulesCreatesTagOnRootAndSubmodules()
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

    /**
     * @return Task
     */
    protected function createTask()
    {
        return new TagTask();
    }
}
