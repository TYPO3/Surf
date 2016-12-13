<?php
namespace TYPO3\Surf\Task;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */


use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * A generic shell task
 *
 */
class ShellTask extends \TYPO3\Surf\Domain\Model\Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface, \TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface
{

    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

    /**
     * @var \TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface
     */
    private $shellReplacePathService;

    /**
     * ShellTask constructor.
     *
     * @param \TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface|null $shellReplacePath
     */
    public function __construct(\TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface $shellReplacePath = null)
    {
        if(null === $shellReplacePath)
        {
            $shellReplacePath = new \TYPO3\Surf\Domain\Service\ShellReplacePathService();
        }
        $this->shellReplacePathService = $shellReplacePath;
    }

    /**
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        if (!isset($options['command'])) {
            throw new \TYPO3\Surf\Exception\InvalidConfigurationException(sprintf('Missing "command" option for %s',  get_class($this)), 1311168045);
        }

        $command = $options['command'];

        $command = $this->replacePaths($command, $application, $deployment);
        $ignoreErrors = isset($options['ignoreErrors']) && $options['ignoreErrors'] === true;
        $logOutput = !(isset($options['logOutput']) && $options['logOutput'] === false);

        $this->shell->executeOrSimulate($command, $node, $deployment, $ignoreErrors, $logOutput);
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
     * Rollback this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     */
    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        if (!isset($options['rollbackCommand'])) {
            return;
        }

        $command = $options['rollbackCommand'];
        $command = $this->replacePaths($command, $application, $deployment);

        $this->shell->execute($command, $node, $deployment, true);
    }

    /**
     * @param $command
     * @param Application $application
     * @param Deployment $deployment
     *
     * @return mixed
     */
    public function replacePaths($command, Application $application, Deployment $deployment)
    {
        return $this->shellReplacePathService->replacePaths($command, $application, $deployment);
    }
}
