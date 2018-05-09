<?php

namespace TYPO3\Surf\Tests\Unit\Command;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\Surf\Command\DescribeCommand;
use PHPUnit\Framework\TestCase;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Integration\FactoryInterface;

class DescribeCommandTest extends TestCase
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
        $this->application->setOption('rsyncExcludes', array('.git', 'web/fileadmin', 'web/uploads'));
        $this->application->setOption('TYPO3\\Surf\\Task\\Transfer\\RsyncTask[rsyncExcludes]', array('.git', 'web/fileadmin', 'web/uploads'));
        $this->application->addNode($this->node);
        $this->deployment->addApplication($this->application);
        $this->deployment->initialize();
    }

    /**
     * @test
     */
    public function execute()
    {
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects($this->once())->method('getDeployment')->willReturn($this->deployment);
        $command = new DescribeCommand();
        $command->setFactory($factory);
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'deploymentName' => $this->deployment->getName(),
        ));

        $this->assertEquals('<success>Deployment TestDeployment</success>

Workflow: <success>Simple workflow</success>

Nodes:

  <success>TestNode</success> (hostname)

Applications:

  <success>TestApplication:</success>
    Deployment path: <success></success>
    Options: 
      rsyncExcludes =>
        <success>.git</success>
        <success>web/fileadmin</success>
        <success>web/uploads</success>
      TYPO3\Surf\Task\Transfer\RsyncTask[rsyncExcludes] =>
        <success>.git</success>
        <success>web/fileadmin</success>
        <success>web/uploads</success>
      deploymentPath => <success>NULL</success>
      releasesPath => <success>/releases</success>
      sharedPath => <success>/shared</success>
    Nodes: <success>TestNode</success>
    Detailed workflow: 
      initialize:
      package:
      transfer:
      update:
      migrate:
      finalize:
      test:
      switch:
      cleanup:
', $commandTester->getDisplay());
    }

}
