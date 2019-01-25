<?php
namespace TYPO3\Surf\Task\Php;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Domain\Filesystem\Filesystem;
use TYPO3\Surf\Domain\Filesystem\FilesystemInterface;
use TYPO3\Surf\Domain\Generator\RandomBytesGenerator;
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

    /**
     * @var RandomBytesGeneratorInterface
     */
    private $randomBytesGenerator;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * WebOpcacheResetCreateScriptTask constructor.
     *
     * @param RandomBytesGeneratorInterface $randomBytesGenerator
     * @param FilesystemInterface $filesystem
     */
    public function __construct(RandomBytesGeneratorInterface $randomBytesGenerator = null, FilesystemInterface $filesystem = null)
    {
        if (! $randomBytesGenerator instanceof RandomBytesGeneratorInterface) {
            $randomBytesGenerator = new RandomBytesGenerator();
        }

        if (! $filesystem instanceof FilesystemInterface) {
            $filesystem = new Filesystem();
        }

        $this->filesystem = $filesystem;
        $this->randomBytesGenerator = $randomBytesGenerator;
    }

    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options Supported options: "scriptBasePath" and "scriptIdentifier"
     *
     * @throws TaskExecutionException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $workspacePath = $deployment->getWorkspacePath($application);
        $webDirectory = $application->hasOption('webDirectory') ? $application->getOption('webDirectory') : 'Web';
        $scriptBasePath = isset($options['scriptBasePath']) ? $options['scriptBasePath'] : Files::concatenatePaths([$workspacePath, $webDirectory]);

        if (! isset($options['scriptIdentifier'])) {
            // Store the script identifier as an application option
            $scriptIdentifier = bin2hex($this->randomBytesGenerator->generate(32));
            $application->setOption(WebOpcacheResetExecuteTask::class . '[scriptIdentifier]', $scriptIdentifier);
        } else {
            $scriptIdentifier = $options['scriptIdentifier'];
        }

        $localhost = new Node('localhost');
        $localhost->onLocalhost();

        $commands = [
            'cd ' . escapeshellarg($scriptBasePath),
            'rm -f surf-opcache-reset-*',
        ];

        $this->shell->executeOrSimulate($commands, $localhost, $deployment);

        if (! $deployment->isDryRun()) {
            $scriptFilename = sprintf('%s/surf-opcache-reset-%s.php', $scriptBasePath, $scriptIdentifier);
            $result = $this->filesystem->put($scriptFilename, '<?php
                if (function_exists("opcache_reset")) {
                    opcache_reset();
                }
                @unlink(__FILE__);
                echo "success";
            ');

            if ($result === false) {
                throw new TaskExecutionException('Could not write file "' . $scriptFilename . '"', 1421932414);
            }
        }
    }
}
