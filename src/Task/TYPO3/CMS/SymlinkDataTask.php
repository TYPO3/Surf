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
use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * A symlink task for linking the shared data directory
 * If the symlink target has folder, the folders themselves must exist!
 */
class SymlinkDataTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Executes this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);
        $targetReleasePath = $deployment->getApplicationReleasePath($application);
        $webDirectory = $options['webDirectory'];
        $relativeDataPath = $relativeDataPathFromWeb = '../../shared/Data';
        if ($webDirectory !== '') {
            $relativeDataPathFromWeb = str_repeat('../', substr_count(trim($webDirectory, '/'), '/') + 1) . $relativeDataPath;
        }
        $absoluteWebDirectory = rtrim("$targetReleasePath/$webDirectory", '/');

        $commands[] = 'cd ' . escapeshellarg($targetReleasePath);

        foreach ($options['symlinkDataFolders'] as $directory) {
            $commands[] = sprintf('{ [ -d %1$s ] || mkdir -p %1$s; }', escapeshellarg($relativeDataPath . '/' . $directory));
            $commands[] = sprintf('ln -sf %1$s %2$s', escapeshellarg($relativeDataPathFromWeb . '/' . $directory), escapeshellarg($absoluteWebDirectory . '/' . $directory));
        }

        foreach ($options['directories'] as $directory) {
            $directory = trim($directory, '\\/');
            $targetDirectory = Files::concatenatePaths([$relativeDataPath, $directory]);
            $commands[] = sprintf('{ [ -d %1$s ] || mkdir -p %1$s; }', escapeshellarg($targetDirectory));
            $commands[] = sprintf('ln -sf %1$s %2$s', escapeshellarg(str_repeat('../', substr_count(trim($directory, '/'), '/')) . $targetDirectory), escapeshellarg($directory));
        }
        $this->shell->executeOrSimulate($commands, $node, $deployment);
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
        $resolver->setDefault('webDirectory', '');
        $resolver->setDefault('directories', []);
        $resolver->setDefault('symlinkDataFolders', []);
        $resolver->setAllowedTypes('symlinkDataFolders', 'array');
        $resolver->setNormalizer('directories', function (Options $options, $value) {
            if (is_array($value)) {
                return $value;
            }

            return [];
        });

        $resolver->setNormalizer('webDirectory', function (Options $options, $value) {
            return trim($value, '\\/');
        });
    }
}
