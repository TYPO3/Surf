<?php
namespace TYPO3\Surf\Task;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use Symfony\Component\Process\ProcessBuilder;
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
    protected $requiredOptions = array('sourceHost', 'sourceUser', 'sourcePassword', 'sourceDatabase','targetHost', 'targetUser', 'targetPassword', 'targetDatabase');

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

        $dumpCommand = new ProcessBuilder();
        $dumpCommand->setPrefix('mysqldump');
        $dumpCommand->setArguments(
            array(
                '-h',
                $options['sourceHost'],
                '-u',
                $options['sourceUser'],
                '-p' . $options['sourcePassword'],
                $options['sourceDatabase']
            )
        );

        $mysqlCommand = new ProcessBuilder();
        $mysqlCommand->setPrefix('mysql');
        $mysqlCommand->setArguments(
            array(
                '-h',
                $options['targetHost'],
                '-u',
                $options['targetUser'],
                '-p' . $options['targetPassword'],
                $options['targetDatabase']
            )
        );

        $arguments = array();
        $username = isset($options['username']) ? $options['username'] . '@' : '';
        $hostname = $node->getHostname();
        $arguments[] = $username . $hostname;
        if ($node->hasOption('port')) {
            $arguments[] = '-P';
            $arguments[] = $node->getOption('port');
        }
        $arguments[] = $mysqlCommand->getProcess()->getCommandLine();
        $sshCommand = new ProcessBuilder();
        $sshCommand->setPrefix('ssh');
        $sshCommand->setArguments($arguments);

        $command = $dumpCommand->getProcess()->getCommandLine()
            . ' | '
            . $sshCommand->getProcess()->getCommandLine();

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
