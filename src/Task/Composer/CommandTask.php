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
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * Runs a custom composer command
 *
 * It takes the following options:
 *
 * * composerCommandPath - Path to the composer binary
 * * command - The composer command to run
 * * nodeName - The name of the node where the composer command should run.
 * * arguments (optional) - Array of arguments to pass to the composer command, default `--no-ansi --no-interaction`
 * * additionalArguments (optional) - Array of additional arguments to pass to composer and keep default arguments
 * * suffix (optional) - Array, string or null with the suffix command, either `['2>&1']`, `[]`, `'2>&1'`, `''` or `null`
 * * useApplicationWorkspace (optional) - If true Surf uses the workspace path, else it uses the release path of the application.
 *
 * Example:
 *  $workflow->defineTask('My\\Distribution\\DefinedTask\\RunBuildScript', \TYPO3\Surf\Task\Composer\CommandTask::class, [
 *      'composerCommandPath' => '/usr/local/bin/composer',
 *      'nodeName' => 'localhost',
 *      'command' => 'run-script',
 *      'additionalArguments' => ['build'],
 *      'useApplicationWorkspace' => true
 *  ]);
 *  $workflow->afterTask('TYPO3\\Surf\\DefinedTask\\Composer\\LocalInstallTask', 'My\\Distribution\\DefinedTask\\RunBuildScript', $application);
 *
 *  `composer 'run-script' '--no-ansi' '--no-interaction' 'build' 2>&1$`
 */
class CommandTask extends AbstractComposerTask
{

    /**
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @throws InvalidConfigurationException
     * @throws TaskExecutionException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);

        $this->command = $options['command'];
        $this->arguments = $options['arguments'];
        $this->suffix = $options['suffix'];

        parent::execute($node, $application, $deployment, $options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function resolveOptions(OptionsResolver $resolver)
    {
        parent::resolveOptions($resolver);
        $resolver->setDefaults([
            'command' => null,
            'arguments' => ['--no-ansi', '--no-interaction'],
            'suffix' => ['2>&1']
        ]);

        $resolver
            ->setRequired('command')
            ->setAllowedTypes('command', 'string')
            ->setNormalizer('command', static function (Options $options, $value) {
                return escapeshellarg($value);
            });

        $resolver
            ->setAllowedTypes('arguments', 'array')
            ->setNormalizer('arguments', static function (Options $options, $value) {
                return array_map('escapeshellarg', $value);
            });

        $resolver
            ->setAllowedTypes('suffix', ['array', 'string', 'null'])
            ->setAllowedValues('suffix', [['2>&1'], [], '2>&1', '', null])
            ->setNormalizer('suffix', static function (Options $options, $value) {
                $value = ($value === '') ? null : $value;
                return (array)$value;
            });
    }
}
