<?php

namespace TYPO3\Surf\Tests\Unit\Domain\Service;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandService;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Domain\Service\TaskFactory;
use TYPO3\Surf\Exception as SurfException;
use TYPO3\Surf\Task\CreateArchiveTask;
use TYPO3\Surf\Tests\Unit\KernelAwareTrait;

class TaskFactoryTest extends TestCase
{
    use KernelAwareTrait;

    /**
     * @var TaskFactory
     */
    protected $subject;

    protected function setUp(): void
    {
        $container = self::getKernel()->getContainer();
        $this->subject = new TaskFactory();
        $this->subject->setContainer($container);
    }

    /**
     * @test
     */
    public function createTaskInstance(): void
    {
        $this->assertInstanceOf(CreateArchiveTask::class, $this->subject->createTaskInstance(CreateArchiveTask::class));
    }

    /**
     * @test
     */
    public function createSyntheticServiceIfNotExists(): void
    {
        /** @var CustomTask $customTask */
        $customTask = $this->subject->createTaskInstance(CustomTask::class);
        $this->assertNotNull($customTask->getShell());
        $this->assertInstanceOf(CustomTask::class, $customTask);
    }

    /**
     * @test
     */
    public function createTaskInstanceThrowsExceptionClassIsNotOfCorrectSubclass(): void
    {
        $task = new class {
        };
        $this->expectException(SurfException::class);
        $this->subject->createTaskInstance(get_class($task));
    }
}

class CustomTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
    }

    public function getShell(): ShellCommandService
    {
        return $this->shell;
    }
}
