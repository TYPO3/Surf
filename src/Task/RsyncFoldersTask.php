<?php
namespace TYPO3\Surf\Task;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * A generic shell task
 *
 */
class RsyncFoldersTask extends \TYPO3\Surf\Domain\Model\Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface, \TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface
{
    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

    /**
     * @var \TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface
     */
    private $shellReplacePathService;

    /**
     * RsyncFoldersTask constructor.
     *
     * @param \TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface|null $shellReplacePathService
     */
    public function __construct(\TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface $shellReplacePathService = null)
    {
        if(null === $shellReplacePathService)
        {
            $shellReplacePathService = new \TYPO3\Surf\Domain\Service\ShellReplacePathService();
        }
        $this->shellReplacePathService = $shellReplacePathService;
    }

    /**
     * Executes this task
     *
     * Options:
     *   command: The command to execute
     *   rollbackCommand: The command to execute as a rollback (optional)
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        if (!isset($options['folders'])) {
            return;
        }
        $folders = $options['folders'];
        if (!is_array($folders)) {
            $folders = array($folders);
        }

        $commands = array();

        $username = isset($options['username']) ? $options['username'] . '@' : '';
        $hostname = $node->getHostname();
        $port = $node->hasOption('port') ? '-P ' . escapeshellarg($node->getOption('port')) : '';

        foreach ($folders as $folderPair) {
            if (!is_array($folderPair) || count($folderPair) !== 2) {
                throw new InvalidConfigurationException('Each rsync folder definition must be an array of exactly two folders', 1405599056);
            }
            $sourceFolder = rtrim($this->replacePaths($folderPair[0], $application, $deployment), '/') . '/';
            $targetFolder = rtrim($this->replacePaths($folderPair[1], $application, $deployment), '/') . '/';
            $commands[] = "rsync -avz --delete -e ssh {$sourceFolder} {$username}{$hostname}:{$targetFolder}";
        }

        $ignoreErrors = isset($options['ignoreErrors']) && $options['ignoreErrors'] === true;
        $logOutput = !(isset($options['logOutput']) && $options['logOutput'] === false);

        $localhost = new Node('localhost');
        $localhost->setHostname('localhost');

        $this->shell->executeOrSimulate($commands, $localhost, $deployment, $ignoreErrors, $logOutput);
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
     * @param $command
     * @param Application $application
     * @param Deployment $deployment
     *
     * @return mixed|string
     */
    public function replacePaths($command, Application $application, Deployment $deployment)
    {
        return $this->shellReplacePathService->replacePaths($command, $application, $deployment);
    }


}
