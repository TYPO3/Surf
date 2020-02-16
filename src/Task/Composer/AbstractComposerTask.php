<?php

namespace TYPO3\Surf\Task\Composer;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * Installs the composer packages based on a composer.json file in the projects root folder
 */
abstract class AbstractComposerTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Command to run
     *
     * @var string
     */
    protected $command = '';

    /**
     * Arguments for the command
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * Suffix for the command
     *
     * @var array
     */
    protected $suffix = ['2>&1'];

    /**
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     *
     * @throws InvalidConfigurationException
     * @throws TaskExecutionException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);

        if ($options['useApplicationWorkspace']) {
            $composerRootPath = $deployment->getWorkspacePath($application);
        } else {
            $composerRootPath = $deployment->getApplicationReleasePath($application);
        }

        if ($options['nodeName'] !== null) {
            $node = $deployment->getNode($options['nodeName']);
            if ($node === null) {
                throw new InvalidConfigurationException(sprintf('Node "%s" not found', $options['nodeName']), 1369759412);
            }
        }

        if ($this->composerManifestExists($composerRootPath, $node, $deployment)) {
            $commands = $this->buildComposerCommands($composerRootPath, $options);
            $this->shell->executeOrSimulate($commands, $node, $deployment);
        }
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     *
     * @throws InvalidConfigurationException
     * @throws TaskExecutionException
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * Build the composer command in the given $path.
     *
     * @param string $manifestPath
     * @param array $options
     *
     * @return array
     * @throws TaskExecutionException
     */
    private function buildComposerCommands($manifestPath, array $options)
    {
        $arguments = array_merge(
            [escapeshellcmd($options['composerCommandPath']), $this->command],
            $this->arguments,
            array_map('escapeshellarg', $options['additionalArguments']),
            $this->suffix
        );
        $script = implode(' ', $arguments);

        return [
            'cd ' . escapeshellarg($manifestPath),
            $script,
        ];
    }

    /**
     * Checks if a composer manifest exists in the directory at the given path.
     *
     * If no manifest exists, a log message is recorded.
     *
     * @param string $path
     * @param Node $node
     * @param Deployment $deployment
     *
     * @return bool
     */
    private function composerManifestExists($path, Node $node, Deployment $deployment)
    {
        // In dry run mode, no checkout is there, this we must not assume a composer.json is there!
        if ($deployment->isDryRun()) {
            return false;
        }
        $composerJsonPath = Files::concatenatePaths([$path, 'composer.json']);
        $composerJsonExists = $this->shell->executeOrSimulate('test -f ' . escapeshellarg($composerJsonPath), $node, $deployment, true);
        if ($composerJsonExists === false) {
            $deployment->getLogger()->debug('No composer.json found in path "' . $composerJsonPath . '"');

            return false;
        }

        return true;
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('composerCommandPath');

        $resolver->setDefault('additionalArguments', [])
                 ->setNormalizer(
                     'additionalArguments',
                     static function (Options $options, $value) {
                         return (array)$value;
                     }
                 );

        $resolver->setDefault('useApplicationWorkspace', false);
        $resolver->setDefault('nodeName', null);
        $resolver->setAllowedTypes('additionalArguments', ['array', 'string']);
    }
}
