<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Application;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use TYPO3\Surf\Application\Laravel;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Tests\Unit\FluidPromise;

class LaravelTest extends TestCase
{
    /**
     * @var Laravel
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new Laravel();
    }

    /**
     * @test
     */
    public function registerTasks(): void
    {
        $deployment = $this->prophesize(Deployment::class);
        $deployment->getForceRun()->willReturn(false);
        $deployment->provideBoolOption('initialDeployment')->willReturn(false);

        $workflow = $this->prophesize(Workflow::class);
        $workflow->addTask(Argument::any(), Argument::any(), $this->subject)->will(new FluidPromise());
        $workflow->afterTask(Argument::any(), Argument::any(), $this->subject)->will(new FluidPromise());
        $workflow->afterStage(Argument::any(), Argument::any(), $this->subject)->will(new FluidPromise());
        $workflow->defineTask(Argument::any(), Argument::any(), Argument::type('array'))->will(new FluidPromise());

        $this->subject->registerTasks($workflow->reveal(), $deployment->reveal());
    }
}
