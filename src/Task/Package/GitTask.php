<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\Package;

use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Task\Git\AbstractCheckoutTask;

/**
 * A Git package task.
 *
 * Package an application by doing a local git update / clone before using the configured "transferMethod" to transfer
 * assets to the application node(s).
 *
 * It takes the following options:
 *
 * * repositoryUrl - The git remote to use.
 * * fetchAllTags (optional) - If true, make a fetch on tags.
 * * gitPostCheckoutCommands (optional) - An array with commands to execute after checkout.
 * * hardClean (optional) - If true, execute a hard clean.
 * * recursiveSubmodules (optional) - If true, handle submodules recursive.
 * * verbose (optional) - If true, output verbose information from git.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\Package\GitTask', [
 *                  'repositoryUrl' => 'git@github.com:TYPO3/Surf.git',
 *                  'verbose' => true,
 *                  'recursiveSubmodules' => true,
 *                  'fetchAllTags' => true,
 *                  'hardClean' => true,
 *                  'gitPostCheckoutCommands' => [
 *                      '/var/www/outerspace' => 'composer install'
 *                  ]
 *              ]
 *          ]
 *      );
 */
class GitTask extends AbstractCheckoutTask
{
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $options = $this->configureOptions($options);

        $localCheckoutPath = $deployment->getWorkspacePath($application);

        $localhost = $deployment->createLocalhostNode();

        $sha1 = $this->executeOrSimulateGitCloneOrUpdate($localCheckoutPath, $localhost, $deployment, $options);

        $this->executeOrSimulatePostGitCheckoutCommands($localCheckoutPath, $sha1, $localhost, $deployment, $options);
    }

    protected function resolveOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('repositoryUrl');
    }
}
