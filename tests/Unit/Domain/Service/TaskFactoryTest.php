<?php

declare(strict_types=1);

namespace TYPO3\Surf\Tests\Unit\Domain\Service;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;
use TYPO3\Surf\Domain\Service\TaskFactory;
use TYPO3\Surf\Task\CreateArchiveTask;
use TYPO3\Surf\Tests\Unit\KernelAwareTrait;
use UnexpectedValueException;

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
        self::assertInstanceOf(
            CreateArchiveTask::class,
            $this->subject->createTaskInstance(CreateArchiveTask::class)
        );
    }

    /**
     * @test
     */
    public function createSyntheticServiceIfNotExists(): void
    {
        /** @var CustomTask $customTask */
        $customTask = $this->subject->createTaskInstance(CustomTask::class);

        self::assertNotNull($customTask->getShell());
        self::assertInstanceOf(CustomTask::class, $customTask);
    }

    /**
     * @test
     */
    public function createTaskInstanceThrowsExceptionClassIsNotOfCorrectSubclass(): void
    {
        $task = new class {
        };

        $this->expectException(UnexpectedValueException::class);

        $this->subject->createTaskInstance(get_class($task));
    }
}
