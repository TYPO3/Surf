<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\Generic;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * Creates symlinks on target node.
 *
 * It takes the following options:
 *
 * * symlinks - An array of symlinks to create. The array index is the link to be created (relative to the current application
 *   release path). The value is the path to the existing file/directory (absolute or relative to the link).
 *
 * Example:
 *  $options['symlinks'] = array(
 *      'Web/foobar' => '/tmp/foobar', # An absolute link
 *      'Web/foobaz' => '../../../shared/Data/foobaz', # A relative link into the shared folder
 *  );
 */
class CreateSymlinksTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $options = $this->configureOptions($options);

        if (empty($options['symlinks'])) {
            return;
        }

        $baseDirectory = $options['genericSymlinksBaseDir'] ?: $deployment->getApplicationReleasePath($node);

        $commands = [
            'cd ' . $baseDirectory,
        ];

        foreach ($options['symlinks'] as $linkPath => $sourcePath) {
            // creates empty directory if path does not exist
            if ($options['createNonExistingSharedDirectories'] === true) {
                $folderDepth = substr_count($linkPath, '/');
                $changedSourceDirectoryAccordingToDeepnessOfLinkPath = $this->changeDeepnessOfPath($sourcePath, $folderDepth);
                $commands[] = sprintf('test -e %s || mkdir -p %s', $changedSourceDirectoryAccordingToDeepnessOfLinkPath, $changedSourceDirectoryAccordingToDeepnessOfLinkPath);
            }

            $commands[] = sprintf('ln -s %s %s', $sourcePath, $linkPath);
        }

        $this->shell->executeOrSimulate($commands, $node, $deployment);
    }

    protected function changeDeepnessOfPath(string $path, int $level = 0): string
    {
        if ($level === 0 || substr($path, 0, 1) === '/') {
            return $path;
        }
        $directoryParts = explode(DIRECTORY_SEPARATOR, $path);
        return implode(DIRECTORY_SEPARATOR, array_splice($directoryParts, $level));
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
        $resolver->setDefault('symlinks', []);
        $resolver->setAllowedTypes('symlinks', 'array');

        $resolver->setDefault('createNonExistingSharedDirectories', true);
        $resolver->setAllowedTypes('createNonExistingSharedDirectories', 'bool');

        $resolver->setDefault('genericSymlinksBaseDir', null);
        $resolver->setAllowedTypes('genericSymlinksBaseDir', ['string', 'null']);
        $resolver->setNormalizer('genericSymlinksBaseDir', fn (Options $options, $value) => ! empty($value) ? $value : null);
    }
}
