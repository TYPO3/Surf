<?php

declare(strict_types=1);

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

/**
 * Installs the composer packages based on a composer.json file in the projects root folder
 */
abstract class AbstractComposerTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Command to run
     */
    protected string $command = '';

    /**
     * Arguments for the command
     */
    protected array $arguments = [];

    /**
     * Suffix for the command
     */
    protected array $suffix = ['2>&1'];

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $options = $this->configureOptions($options);

        if ($options['useApplicationWorkspace']) {
            $composerRootPath = $deployment->getWorkspaceWithProjectRootPath($application);
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

    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * Build the composer command in the given $path.
     */
    private function buildComposerCommands(string $manifestPath, array $options): array
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
     */
    private function composerManifestExists(string $path, Node $node, Deployment $deployment): bool
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

    protected function resolveOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('composerCommandPath');

        $resolver->setDefault('additionalArguments', [])
            ->setNormalizer('additionalArguments', static function (Options $options, $value): array {
                return (array)$value;
            });

        $resolver->setDefault('useApplicationWorkspace', false);
        $resolver->setDefault('nodeName', null);
        $resolver->setAllowedTypes('additionalArguments', ['array', 'string']);
    }
}
