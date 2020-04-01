<?php

namespace TYPO3\Surf\Tests\Unit\Command;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\Surf\Command\DeployCommand;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Integration\FactoryInterface;

final class DeployCommandTest extends TestCase
{

    /**
     * @var DeployCommand
     */
    protected $subject;

    /**
     * @var FactoryInterface|ObjectProphecy
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->prophesize(FactoryInterface::class);
        $this->subject = new DeployCommand($this->factory->reveal());
    }

    /**
     * @test
     */
    public function executeForceRun(): void
    {
        $deployment = $this->prophesize(Deployment::class);
        $deployment->deploy()->shouldBeCalledOnce();
        $deployment->getStatus()->willReturn(Deployment::STATUS_SUCCESS);
        $this->factory->getDeployment('Foo', Argument::exact(null), Argument::exact(false), Argument::exact(true), Argument::exact(true))->willReturn($deployment);

        $commandTester = new CommandTester($this->subject);
        $commandTester->execute([
            'deploymentName' => 'Foo',
            '--force' => true,
        ]);
    }
}
