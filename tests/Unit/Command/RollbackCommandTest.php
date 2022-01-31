<?php

namespace TYPO3\Surf\Tests\Unit\Command;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\Surf\Command\RollbackCommand;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Integration\FactoryInterface;

class RollbackCommandTest extends TestCase
{
    /**
     * @test
     */
    public function executeSuccessfully(): void
    {
        $deployment = $this->prophesize(Deployment::class);
        $deployment->getStatus()->willReturn(Deployment::STATUS_SUCCESS)->shouldBeCalledOnce();
        $deployment->rollback(false)->shouldBeCalledOnce();

        $factory = $this->prophesize(FactoryInterface::class);
        $factory->getDeployment('foo', '.surf', false, false)->willReturn($deployment);

        $command = new RollbackCommand($factory->reveal());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['deploymentName' => 'foo', '--configurationPath' => '.surf', '--simulate' => false]);

        self::assertSame('', $commandTester->getDisplay());
    }
}
