<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\Git;

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * A task which can be used to tag a git repository and its submodules
 *
 * It takes the following options:
 *
 * * tagName - The tag name to use
 * * description - The description for the tag
 * * recurseIntoSubmodules - If true, tag submodules as well (optional)
 * * submoduleTagNamePrefix - Prefix for the submodule tags (optional)
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\Git\TagTask', [
 *                  'tagName' => 'earth2',
 *                  'description' => 'Another release to save the planet',
 *                  'recurseIntoSubmodules' => true,
 *                  'submoduleTagNamePrefix' => 'sub-'
 *              ]
 *          ]
 *      );
 */
/**
 * @deprecated Will be removed in version 4.0
 */
class TagTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * @param array<string,mixed> $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->logger->warning('This task is deprecated and will be removed in Version 4.0');
        $this->validateOptions($options);
        $options = $this->processOptions($options, $deployment);

        $targetPath = $deployment->getApplicationReleasePath($node);
        $this->shell->executeOrSimulate(sprintf('cd ' . $targetPath . '; git tag -f -a -m %s %s', escapeshellarg($options['description']), escapeshellarg($options['tagName'])), $node, $deployment);
        if (isset($options['recurseIntoSubmodules']) && $options['recurseIntoSubmodules'] === true) {
            $submoduleCommand = escapeshellarg(sprintf('git tag -f -a -m %s %s', escapeshellarg($options['description']), escapeshellarg($options['submoduleTagNamePrefix'] . $options['tagName'])));
            $this->shell->executeOrSimulate(sprintf('cd ' . $targetPath . '; git submodule foreach %s', $submoduleCommand), $node, $deployment);
        }

        if (isset($options['pushTag']) && $options['pushTag'] === true) {
            $this->shell->executeOrSimulate(sprintf('cd ' . $targetPath . '; git push %s %s', escapeshellarg($options['remote']), escapeshellarg($options['tagName'])), $node, $deployment);
            if (isset($options['recurseIntoSubmodules']) && $options['recurseIntoSubmodules'] === true) {
                $submoduleCommand = escapeshellarg(sprintf('git push %s %s', escapeshellarg($options['remote']), escapeshellarg($options['submoduleTagNamePrefix'] . $options['tagName'])));
                $this->shell->executeOrSimulate(sprintf('cd ' . $targetPath . '; git submodule foreach %s', $submoduleCommand), $node, $deployment);
            }
        }
    }

    /**
     * @codeCoverageIgnore
     * @param array<string,mixed> $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * @param array<string,string|null> $options
     */
    protected function validateOptions(array $options): void
    {
        if (!isset($options['tagName'])) {
            throw new InvalidConfigurationException('Missing "tagName" option for TagTask', 1314186541);
        }

        if (!isset($options['description'])) {
            throw new InvalidConfigurationException('Missing "description" option for TagTask', 1314186553);
        }
    }

    /**
     * @param array<string, string> $options
     * @return array<string, string>
     */
    protected function processOptions(array $options, Deployment $deployment): array
    {
        foreach (['tagName', 'description'] as $optionName) {
            $options[$optionName] = str_replace(
                [
                    '{releaseIdentifier}',
                    '{deploymentName}'
                ],
                [
                    $deployment->getReleaseIdentifier() ?? '',
                    $deployment->getName()
                ],
                $options[$optionName]
            );
        }

        if (!isset($options['submoduleTagNamePrefix'])) {
            $options['submoduleTagNamePrefix'] = '';
        }

        if (!isset($options['remote'])) {
            $options['remote'] = 'origin';
            return $options;
        }
        return $options;
    }
}
