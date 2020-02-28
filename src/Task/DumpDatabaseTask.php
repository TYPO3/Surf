<?php
namespace TYPO3\Surf\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Process\Process;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * This task dumps a complete database from a source system to a target system.
 *
 * It takes the following options:
 *
 * * sourceHost - The host on which the source database is located.
 * * sourceUser - The database user of the source database.
 * * sourcePassword - The password of the source user.
 * * sourceDatabase - The source database.
 * * targetHost - The host on which the target database is located.
 * * targetUser - The database user og the target database.
 * * targetPassword - The password of the target user.
 * * targetDatabase - The target database.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\DumpDatabaseTask', [
 *              sourceHost => 'from.outerspace.all',
 *              sourceUser => 'e_t',
 *              sourcePassword => 'phoneHome',
 *              sourceDatabase => 'spaceship',
 *              targetHost => 'localhost',
 *              targetUser => 'elliot',
 *              targetPassword => 'human',
 *              targetDatabase => 'house'
 *          ]
 *      );
 */
class DumpDatabaseTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);

        $dumpCommand = new Process([
            'mysqldump',
            '-h',
            $options['sourceHost'],
            '-u',
            $options['sourceUser'],
            '-p' . $options['sourcePassword'],
            $options['sourceDatabase']
        ]);

        $mysqlCommand = new Process([
            'mysql',
            '-h',
            $options['targetHost'],
            '-u',
            $options['targetUser'],
            '-p' . $options['targetPassword'],
            $options['targetDatabase']
        ]);

        $sshCommand = ['ssh'];
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
        $localhost->onLocalhost();

        $this->shell->executeOrSimulate($command, $localhost, $deployment);
    }

    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'sourceHost',
            'sourceUser',
            'sourcePassword',
            'sourceDatabase',
            'targetHost',
            'targetUser',
            'targetPassword',
            'targetDatabase'
        ]);
    }
}
