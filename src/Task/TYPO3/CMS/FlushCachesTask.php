<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Application\TYPO3\CMS;
use TYPO3\Surf\DeprecationMessageFactory;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * Clear TYPO3 caches
 * This task requires the extension typo3_console.
 */
class FlushCachesTask extends AbstractCliTask
{
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
        $this->ensureApplicationIsTypo3Cms($application);

        $options = $this->configureOptions($options);

        $cliArguments = $this->getSuitableCliArguments($node, $application, $deployment, $options);
        if (empty($cliArguments)) {
            $deployment->getLogger()->warning('Neither Extension "typo3_console" nor "coreapi" was found! Make sure one is available in your project, or remove this task (' . __CLASS__ . ') in your deployment configuration!');
            return;
        }
        $this->executeCliCommand(
            $cliArguments,
            $node,
            $application,
            $deployment,
            $options
        );
    }

    /**
     * @param Node $node
     * @param CMS $application
     * @param Deployment $deployment
     * @param array $options
     * @return array
     */
    protected function getSuitableCliArguments(Node $node, CMS $application, Deployment $deployment, array $options = [])
    {
        switch ($this->getAvailableCliPackage($node, $application, $deployment, $options)) {
            case 'typo3_console':
                return array_merge([$this->getConsoleScriptFileName($node, $application, $deployment, $options), 'cache:flush'], $options['arguments']);
            case 'coreapi':
                $deployment->getLogger()->warning(DeprecationMessageFactory::createDeprecationWarningForCoreApiUsage());
                return [$this->getCliDispatchScriptFileName($options), 'extbase', 'cacheapi:clearallcaches'];
            default:
                return [];
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('arguments', ['--force'])
            ->setAllowedTypes('arguments', ['array', 'string'])
            ->setNormalizer('arguments', function (Options $options, $value) {
                return (array)$value;
            });
    }
}
