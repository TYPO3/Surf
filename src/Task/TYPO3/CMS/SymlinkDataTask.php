<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\TYPO3\CMS;

use Neos\Utility\Files;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
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

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $commands = [];
        $options = $this->configureOptions($options);
        $targetReleasePath = $deployment->getApplicationReleasePath($node);
        $webDirectory = $options['webDirectory'];
        $relativeDataPath = $relativeDataPathFromWeb = '../../shared/Data';
        if ($webDirectory !== '') {
            $relativeDataPathFromWeb = str_repeat('../', substr_count(trim($webDirectory, '/'), '/') + 1) . $relativeDataPath;
        }
        $absoluteWebDirectory = Files::concatenatePaths([$targetReleasePath, $webDirectory]);

        $commands[] = 'cd ' . escapeshellarg($targetReleasePath);

        foreach ($options['symlinkDataFolders'] as $directory) {
            $commands[] = sprintf('mkdir -p %1$s', escapeshellarg(Files::concatenatePaths([$relativeDataPath, $directory])));
            $commands[] = sprintf(
                'ln -sf %1$s %2$s',
                escapeshellarg(Files::concatenatePaths([$relativeDataPathFromWeb, $directory])),
                escapeshellarg(Files::concatenatePaths([$absoluteWebDirectory, $directory]))
            );
        }

        foreach ($options['directories'] as $directory) {
            $directory = trim($directory, '\\/');
            $targetDirectory = Files::concatenatePaths([$relativeDataPath, $directory]);
            $commands[] = sprintf('mkdir -p %1$s', escapeshellarg($targetDirectory));
            $commands[] = sprintf(
                'ln -sf %1$s %2$s',
                escapeshellarg(str_repeat('../', substr_count(trim($directory, '/'), '/')) . $targetDirectory),
                escapeshellarg($directory)
            );
        }
        $this->shell->executeOrSimulate($commands, $node, $deployment);
    }

    /**
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->execute($node, $application, $deployment, $options);
    }

    protected function resolveOptions(OptionsResolver $resolver): void
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

        $resolver->setNormalizer('webDirectory', fn (Options $options, $value): string => trim($value, '\\/'));
    }
}
