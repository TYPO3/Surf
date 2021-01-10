<?php

namespace TYPO3\Surf\Tests\Unit\Application\TYPO3;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Tests\Unit\FluidPromise;

class CMSTest extends TestCase
{
    /**
     * @var CMS
     */
    protected $subject;

    protected function setUp(): void
    {
        $this->subject = new CMS();
    }

    /**
     * @test
     */
    public function setContextSuccessfully(): void
    {
        $this->subject->setContext('Production');
        self::assertSame($this->subject->getContext(), 'Production');
    }

    /**
     * @test
     */
    public function registerTasks(): void
    {
        /* @var Deployment|\Prophecy\Prophecy\ObjectProphecy $workflow */
        $deployment = $this->prophesize(Deployment::class);

        /* @var Workflow|\Prophecy\Prophecy\ObjectProphecy $workflow */
        $workflow = $this->prophesize(Workflow::class);
        $workflow->addTask(Argument::any(), Argument::any(), $this->subject)->will(new FluidPromise());
        $workflow->afterTask(Argument::any(), Argument::any(), $this->subject)->will(new FluidPromise());
        $workflow->afterStage(Argument::any(), Argument::any(), $this->subject)->will(new FluidPromise());
        $workflow->defineTask(Argument::any(), Argument::any(), Argument::type('array'))->will(new FluidPromise());

        $this->subject->registerTasks($workflow->reveal(), $deployment->reveal());
    }
}
