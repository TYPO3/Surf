<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * Creates the package states file and removes all not active packages from the according folders
 * This task is meant to be used for local packaging only
 */
class CreatePackageStatesTask extends AbstractCliTask
{
    /**
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return void
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->ensureApplicationIsTypo3Cms($application);
        if (!$this->packageStatesFileExists($node, $application, $deployment, $options)) {
            try {
                $scriptFileName = $this->getConsoleScriptFileName($node, $application, $deployment, $options);
            } catch (InvalidConfigurationException $e) {
                throw new InvalidConfigurationException('No package states file found in the repository and no typo3_console package found to generate it. We cannot proceed.', 1420210956, $e);
            }
            $commandArguments = array($scriptFileName, 'install:generatepackagestates');
            if (!empty($options['removeInactivePackages'])) {
                $commandArguments[] = '--remove-inactive-packages';
            }
            $this->executeCliCommand($commandArguments, $node, $application, $deployment, $options);
        }
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
     * Checks if the package states file exists
     *
     * If no manifest exists, a log message is recorded.
     *
     * @param Node $node
     * @param CMS $application
     * @param Deployment $deployment
     * @param array $options
     * @return bool
     */
    protected function packageStatesFileExists(Node $node, CMS $application, Deployment $deployment, array $options = array())
    {
        $webDirectory = isset($options['webDirectory']) ? trim($options['webDirectory'], '\\/') : '';
        return $this->fileExists($webDirectory . '/typo3conf/PackageStates.php', $node, $application, $deployment, $options);
    }
}
