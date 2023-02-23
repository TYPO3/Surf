<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Tests\Unit\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\Surf\Command\ShowCommand;
use TYPO3\Surf\Integration\FactoryInterface;

class ShowCommandTest extends TestCase
{
    use ProphecyTrait;
    /**
     * @test
     */
    public function executeSuccessfully(): void
    {
        $factory = $this->prophesize(FactoryInterface::class);
        $factory->getDeploymentNames('.surf')->willReturn(['foo', 'bar', 'baz']);
        $factory->getDeploymentsBasePath('.surf')->willReturn('./surf');
        $command = new ShowCommand($factory->reveal());
        $commandTester = new CommandTester($command);
        $commandTester->execute(['--configurationPath' => '.surf']);

        self::assertSame('
<u>Deployments in "./surf":</u>

  foo
  bar
  baz

', $commandTester->getDisplay());
    }
}
