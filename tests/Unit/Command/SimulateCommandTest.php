<?php

namespace TYPO3\Surf\Tests\Unit\Command;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\Surf\Command\RollbackCommand;
use TYPO3\Surf\Command\SimulateCommand;
use PHPUnit\Framework\TestCase;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Integration\FactoryInterface;

class SimulateCommandTest extends TestCase
{
    /**
     * @test
     */
    public function executeSuccessfully(): void
    {
        $deployment = $this->prophesize(Deployment::class);
        $deployment->getStatus()->willReturn(Deployment::STATUS_SUCCESS)->shouldBeCalledOnce();
        $deployment->simulate()->shouldBeCalledOnce();

        $factory = $this->prophesize(FactoryInterface::class);
        $factory->getDeployment('foo', '.surf', true, true, true)->willReturn($deployment);
        $command = new SimulateCommand($factory->reveal());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['deploymentName' => 'foo', '--configurationPath' => '.surf', '--force' => true]);

        $this->assertEquals('', $commandTester->getDisplay());
    }
}
