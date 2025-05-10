<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Task\Php;

use Neos\Utility\Files;
use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Domain\Filesystem\FilesystemInterface;
use TYPO3\Surf\Domain\Generator\RandomBytesGeneratorInterface;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * Create a script to reset the PHP opcache.
 *
 * The task creates a temporary script (locally in the release workspace directory) for resetting the PHP opcache in a
 * later web request. A secondary task will execute an HTTP request and thus execute the script.
 *
 * The opcache reset has to be done in the webserver process, so a simple CLI command would not help.
 *
 * It takes the following options:
 *
 * * scriptBasePath (optional) - The path where the script should be created. Default is `<Workspace Path>/Web`.
 * * scriptIdentifier (optional) - The name of the script. Default is a random string.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\Php\WebOpcacheResetCreateScriptTask', [
 *              'scriptBasePath' => '/var/www/outerspace',
 *              'scriptIdentifier' => 'eraseAllHumans'
 *          ]
 *      );
 */
class WebOpcacheResetCreateScriptTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    private RandomBytesGeneratorInterface $randomBytesGenerator;

    private FilesystemInterface $filesystem;

    public function __construct(RandomBytesGeneratorInterface $randomBytesGenerator, FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->randomBytesGenerator = $randomBytesGenerator;
    }

    /**
     * @param array<string,mixed> $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $options = $this->configureOptions($options);

        $workspacePath = $deployment->getWorkspacePath($application);
        $webDirectory = $application->hasOption('webDirectory') ? $application->getOption('webDirectory') : $application::DEFAULT_WEB_DIRECTORY;
        $scriptBasePath = $options['scriptBasePath'] ?: Files::concatenatePaths([$workspacePath, $webDirectory]);

        if ($options['scriptIdentifier'] === null) {
            $scriptIdentifier = $this->setScriptIdentifier($application);
        } else {
            $scriptIdentifier = $options['scriptIdentifier'];
        }

        $commands = [
            'cd ' . escapeshellarg($scriptBasePath),
            'rm -f surf-opcache-reset-*',
        ];

        $this->shell->executeOrSimulate($commands, $deployment->createLocalhostNode(), $deployment);

        if (! $deployment->isDryRun()) {
            $scriptFilename = sprintf('%s/surf-opcache-reset-%s.php', $scriptBasePath, $scriptIdentifier);

            $result = $this->filesystem->put($scriptFilename, '<?php
if (function_exists("clearstatcache")) {
    // Clear realpath cache
    clearstatcache(true);
}
if (function_exists("opcache_reset")) {
    // Clear opcache
    opcache_reset();
}
@unlink(__FILE__);
echo "success";
');

            if ($result === false) {
                throw TaskExecutionException::webOpcacheResetCreateScriptTaskCouldNotWritFile($scriptFilename);
            }
        }
    }

    /**
     * @param array<string,mixed> $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $options = $this->configureOptions($options);

        if ($options['scriptIdentifier'] === null) {
            $this->setScriptIdentifier($application);
        }
    }

    private function setScriptIdentifier(Application $application): string
    {
        $scriptIdentifier = bin2hex($this->randomBytesGenerator->generate(32));
        $application->setOption(WebOpcacheResetExecuteTask::class . '[scriptIdentifier]', $scriptIdentifier);
        return $scriptIdentifier;
    }

    protected function resolveOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('scriptIdentifier', null);
        $resolver->setDefault('scriptBasePath', null);
    }
}
