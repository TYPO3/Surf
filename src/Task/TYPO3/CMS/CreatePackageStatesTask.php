<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * Creates the package states file and removes all not active packages from the according folders
 */
class CreatePackageStatesTask extends AbstractCliTask
{
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->ensureApplicationIsTypo3Cms($application);
        if (!$this->packageStatesFileExists($node, $application, $deployment, $options)) {
            try {
                $scriptFileName = $this->getConsoleScriptFileName($node, $application, $deployment, $options);
            } catch (InvalidConfigurationException $e) {
                throw new InvalidConfigurationException('No package states file found in the repository and no typo3_console package found to generate it. We cannot proceed.', 1420210956, $e);
            }
            $commandArguments = [$scriptFileName, 'install:generatepackagestates'];
            if (!empty($options['removeInactivePackages'])) {
                $commandArguments[] = '--remove-inactive-packages';
            }
            $this->executeCliCommand($commandArguments, $node, $application, $deployment, $options);
        }
    }

    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * Checks if the package states file exists
     *
     * If no manifest exists, a log message is recorded.
     *
     * @return bool
     */
    protected function packageStatesFileExists(Node $node, CMS $application, Deployment $deployment, array $options = [])
    {
        $webDirectory = isset($options['webDirectory']) ? trim($options['webDirectory'], '\\/') : '';
        return $this->fileExists($webDirectory . '/typo3conf/PackageStates.php', $node, $application, $deployment, $options);
    }
}
