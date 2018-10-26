<?php

namespace TYPO3\Surf\Tests\Unit\Command;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\Surf\Command\DeployCommand;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Integration\FactoryInterface;

final class DeployCommandTest extends TestCase
{

    /**
     * @var Node
     */
    protected $node;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Deployment
     */
    protected $deployment;

    /**
     * @throws \TYPO3\Surf\Exception
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    protected function setUp()
    {
        $this->node = new Node('TestNode');
        $this->node->setHostname('hostname');
        $this->deployment = new Deployment('TestDeployment');
        $this->application = new Application('TestApplication');
        $this->application->addNode($this->node);
        $this->deployment->addApplication($this->application);
        $this->deployment->initialize();
        $this->deployment->setLogger($this->createMock(LoggerInterface::class));
    }

    /**
     * @test
     */
    public function executeForceRun()
    {
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects($this->once())->method('getDeployment')->willReturn($this->deployment);
        $command = new DeployCommand();
        $command->setFactory($factory);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'deploymentName' => $this->deployment->getName(),
            '--force' => true,
        ]);
        $this->deployment->setForceRun(true);
        $this->assertEquals($this->deployment->getStatus(), Deployment::STATUS_SUCCESS);
        $this->assertTrue($this->deployment->getForceRun());
    }
}
