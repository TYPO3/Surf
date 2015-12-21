<?php
namespace TYPO3\Surf\Command;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * Surf command controller
 */
class SurfCommandController extends \TYPO3\Flow\Cli\CommandController
{

    /**
     * @Flow\Inject
     * @var \TYPO3\Surf\Domain\Service\DeploymentService
     */
    protected $deploymentService;

    /**
     * List deployments
     *
     * List available deployments that can be deployed with the surf:deploy command.
     *
     * @param bool $quiet If set, only the deployment names will be output, one per line
     * @param string $configurationPath Path for deployment configuration files
     * @return void
     */
    public function listCommand($quiet = false, $configurationPath = null)
    {
        $deploymentNames = $this->deploymentService->getDeploymentNames($configurationPath);

        if (!$quiet) {
            $this->outputLine('<u>Deployments</u>:' . PHP_EOL);
        }

        foreach ($deploymentNames as $deploymentName) {
            $line = $deploymentName;
            if (!$quiet) {
                $line = '  ' . $line;
            }
            $this->outputLine($line);
        }
    }

    /**
     * Run a deployment
     *
     * @param string $deploymentName The deployment name
     * @param bool $verbose In verbose mode, the log output of the default logger will contain debug messages
     * @param bool $disableAnsi Disable ANSI formatting of output
     * @param string $configurationPath Path for deployment configuration files
     * @return void
     */
    public function deployCommand($deploymentName, $verbose = false, $disableAnsi = false, $configurationPath = null)
    {
        $deployment = $this->deploymentService->getDeployment($deploymentName, $configurationPath);
        if ($deployment->getLogger() === null) {
            $logger = $this->createDefaultLogger($deploymentName, $verbose ? LOG_DEBUG : LOG_INFO, $disableAnsi);
            $deployment->setLogger($logger);
        }
        $deployment->initialize();

        $deployment->deploy();
        $this->response->setExitCode($deployment->getStatus());
    }

    /**
     * Create a default logger with console and file backend
     *
     * @param string $deploymentName
     * @param int $severityThreshold
     * @param bool $disableAnsi
     * @param bool $addFileBackend
     * @return \TYPO3\Flow\Log\Logger
     */
    public function createDefaultLogger($deploymentName, $severityThreshold, $disableAnsi = false, $addFileBackend = true)
    {
        $logger = new \TYPO3\Flow\Log\Logger();
        $console = new \TYPO3\Surf\Log\Backend\AnsiConsoleBackend(array(
            'severityThreshold' => $severityThreshold,
            'disableAnsi' => $disableAnsi
        ));
        $logger->addBackend($console);
        if ($addFileBackend) {
            $file = new \TYPO3\Flow\Log\Backend\FileBackend(array(
                'logFileURL' => FLOW_PATH_DATA . 'Logs/Surf-' . $deploymentName . '.log',
                'createParentDirectories' => true,
                'severityThreshold' => LOG_DEBUG,
                'logMessageOrigin' => false
            ));
            $logger->addBackend($file);
        }
        return $logger;
    }

    /**
     * Describe a deployment
     *
     * @param string $deploymentName
     * @param string $configurationPath Path for deployment configuration files
     * @return void
     */
    public function describeCommand($deploymentName, $configurationPath = null)
    {
        $deployment = $this->deploymentService->getDeployment($deploymentName, $configurationPath);

        $deployment->initialize();

        $this->outputLine('<em> Deployment <b>' . $deployment->getName() . ' </b></em>');
        $this->outputLine();
        $this->outputLine('<u>Workflow</u>: ' . $deployment->getWorkflow()->getName() . PHP_EOL);
        $this->outputLine('<u>Nodes</u>:' . PHP_EOL);
        foreach ($deployment->getNodes() as $node) {
            $this->outputLine('  <b>' . $node->getName() . '</b> (' . $node->getHostname() . ')');
        }
        $this->outputLine(PHP_EOL . '<u>Applications</u>:' . PHP_EOL);
        foreach ($deployment->getApplications() as $application) {
            $this->outputLine('  <b>' . $application->getName() . '</b>' . PHP_EOL);
            $this->outputLine('    <u>Deployment path</u>: ' . $application->getDeploymentPath());
            $this->outputLine('    <u>Options</u>: ');
            foreach ($application->getOptions() as $key => $value) {
                $this->outputLine('      ' . $key . ' => ' . $value);
            }
            $this->outputLine('    <u>Nodes</u>: ' . implode(', ', $application->getNodes()));
        }
    }

    /**
     * Simulate a deployment
     *
     * @param string $deploymentName The deployment name
     * @param bool $verbose In verbose mode, the log output of the default logger will contain debug messages
     * @param bool $disableAnsi Disable ANSI formatting of output
     * @param string $configurationPath Path for deployment configuration files
     * @return void
     */
    public function simulateCommand($deploymentName, $verbose = false, $disableAnsi = false, $configurationPath = null)
    {
        $deployment = $this->deploymentService->getDeployment($deploymentName, $configurationPath);
        if ($deployment->getLogger() === null) {
            $logger = $this->createDefaultLogger($deploymentName, $verbose ? LOG_DEBUG : LOG_INFO, $disableAnsi, false);
            $deployment->setLogger($logger);
        }
        $deployment->initialize();

        $deployment->simulate();
    }
}
