<?php
namespace TYPO3\Surf\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\Process\Process;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;

/**
 * This task dumps a complete database from a source system to a target system
 */
class DumpDatabaseTask extends Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
{
    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

    /**
     * @var array
     */
    protected $requiredOptions = array('sourceHost', 'sourceUser', 'sourcePassword', 'sourceDatabase', 'targetHost', 'targetUser', 'targetPassword', 'targetDatabase');

    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->assertRequiredOptionsExist($options);

        $dumpCommand = new Process(array(
            'mysqldump',
            '-h',
            $options['sourceHost'],
            '-u',
            $options['sourceUser'],
            '-p' . $options['sourcePassword'],
            $options['sourceDatabase']
        ));

        $mysqlCommand = new Process(array(
            'mysql',
            '-h',
            $options['targetHost'],
            '-u',
            $options['targetUser'],
            '-p' . $options['targetPassword'],
            $options['targetDatabase']

        ));

        $sshCommand = array('ssh');
        $username = isset($options['username']) ? $options['username'] . '@' : '';
        $hostname = $node->getHostname();
        $sshCommand[] = $username . $hostname;
        if ($node->hasOption('port')) {
            $sshCommand[] = '-P';
            $sshCommand[] = $node->getOption('port');
        }
        $sshCommand[] = $mysqlCommand->getCommandLine();
        $sshCommand = new Process($sshCommand);

        $command = $dumpCommand->getCommandLine()
            . ' | '
            . $sshCommand->getCommandLine();

        $localhost = new Node('localhost');
        $localhost->setHostname('localhost');

        $this->shell->executeOrSimulate($command, $localhost, $deployment);
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return void
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * @param array $options
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    protected function assertRequiredOptionsExist(array $options)
    {
        foreach ($this->requiredOptions as $optionName) {
            if (!isset($options[$optionName])) {
                throw new \TYPO3\Surf\Exception\InvalidConfigurationException(sprintf('Required option "%s" is not set!', $optionName), 1405592631);
            }
        }
    }
}
