<?php

namespace TYPO3\Surf\Task;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\DeprecationMessageFactory;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

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
        $options = $this->configureOptions($options);
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
     * @param OptionsResolver $resolver
     */
    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['sourceforgeProjectName', 'sourceforgePackageName', 'sourceforgeUserName', 'version', 'files']);
        $resolver->setAllowedTypes('files', 'array');
    }
}
