<?php
namespace TYPO3\Surf\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\DeprecationMessageFactory;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * A task for uploading to sourceforge.
 * @deprecated
 *
 * It takes the following options:
 *
 * * sourceforgeProjectName - The project name at SourceForge.
 * * sourceforgeUserName - The user name to log in at SourceForge.
 * * sourceforgePackageName - The package name of the package that shouldd be uploaded.
 * * version - The version of the project.
 * * files - An array with files to upload to SourceForge.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\SourceforgeUploadTask', [
 *              'sourceforgeProjectName' => 'enterprise',
 *              'sourceforgeUserName' => 'picard',
 *              'sourceforgePackageName' => 'nextGeneration',
 *              'version' => '1.0.0',
 *              'files' => [
 *                  '/var/borg',
 *                  '/var/q',
 *                  '/var/data'
 *              ]
 *          ]
 *      );
 */
class SourceforgeUploadTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $deployment->getLogger()->warning(DeprecationMessageFactory::createGenericDeprecationWarningForTask(__CLASS__));
        $this->checkOptionsForValidity($options);
        $projectName = $options['sourceforgeProjectName'];

        $sourceforgeLogin = $options['sourceforgeUserName'] . ',' . $options['sourceforgeProjectName'];

        $projectDirectory = str_replace(' ', '\ ', sprintf('/home/frs/project/%s/%s/%s/%s/%s', substr($projectName, 0, 1), substr($projectName, 0, 2), $projectName, $options['sourceforgePackageName'], $options['version']));
        $targetHostAndDirectory = escapeshellarg($sourceforgeLogin . '@frs.sourceforge.net:' . $projectDirectory);

        $this->shell->executeOrSimulate('rsync -e ssh ' . implode(' ', $options['files']) . ' ' . $targetHostAndDirectory, $node, $deployment);
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * Check if all required options are given
     *
     * @param array $options
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     */
    protected function checkOptionsForValidity(array $options)
    {
        if (!isset($options['sourceforgeProjectName'])) {
            throw new InvalidConfigurationException('"sourceforgeProjectName" option not set', 1314170122);
        }

        if (!isset($options['sourceforgePackageName'])) {
            throw new InvalidConfigurationException('"sourceforgePackageName" option not set', 1314170132);
        }

        if (!isset($options['sourceforgeUserName'])) {
            throw new InvalidConfigurationException('"sourceforgeUserName" option not set', 1314170145);
        }

        if (!isset($options['version'])) {
            throw new InvalidConfigurationException('"version" option not set', 1314170151);
        }

        if (!isset($options['files'])) {
            throw new InvalidConfigurationException('"files" option for upload not set', 1314170162);
        }

        if (!is_array($options['files'])) {
            throw new InvalidConfigurationException('"files" option for upload is not an array', 1314170175);
        }
    }
}
