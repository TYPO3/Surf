<?php

namespace TYPO3\Surf\Task\Neos\Flow;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use Webmozart\Assert\Assert;

/**
 * This task runs Neos Flow commands
 *
 * It takes the following options:
 *
 * * command (required)
 * * arguments
 * * ignoreErrors (optional)
 * * logOutput (optional)
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions(\TYPO3\Surf\Task\TYPO3\CMS\RunCommandTask::class, [
 *              'command' => 'flow:help',
 *              'arguments => [],
 *              'ignoreErrors' => false,
 *              'logOutput' => true,
 *          ]
 *      );
 */
class RunCommandTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application|Flow $application
     * @param Deployment $deployment
     * @param array $options
     *
     * @throws InvalidConfigurationException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        Assert::isInstanceOf($application, Flow::class, sprintf('Flow application needed for RunCommandTask, got "%s"', get_class($application)));

        $options = $this->configureOptions($options);

        $targetPath = $deployment->getApplicationReleasePath($application);

        $command = sprintf('cd %s && FLOW_CONTEXT=%s ./%s %s', $targetPath, $application->getContext(), $application->getFlowScriptName(), $options['command']);

        $this->shell->executeOrSimulate($command, $node, $deployment, $options['ignoreErrors'], $options['logOutput']);
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
        $resolver->setDefault('ignoreErrors', false);
        $resolver->setDefault('logOutput', true);

        $resolver->setDefault('arguments', []);
        $resolver->setRequired('command');
        $resolver->setNormalizer('command', static function (Options $options, $value) {
            if (!empty($options['arguments'])) {
                return sprintf('%s %s', $value, implode(' ', array_map('escapeshellarg', (array)$options['arguments'])));
            }

            return $value;
        });
    }
}
