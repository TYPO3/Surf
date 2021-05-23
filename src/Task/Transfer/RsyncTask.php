<?php
namespace TYPO3\Surf\Task\Transfer;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Phar;
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
 * A rsync transfer task
 *
 * Copies the application assets from the application workspace to the node using rsync.
 */
class RsyncTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * @var array
     */
    protected $replacePaths = [];

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);

        $localPackagePath = $deployment->getWorkspacePath($application);
        $releasePath = $deployment->getApplicationReleaseBasePath($node);

        if ($options['webDirectory'] !== null) {
            $this->replacePaths['{webDirectory}'] = $options['webDirectory'];
        }

        $remotePath = Files::concatenatePaths([$node->getDeploymentPath(), 'cache', 'transfer']);
        // make sure there is a remote .cache folder
        $command = 'mkdir -p ' . $remotePath;
        $this->shell->executeOrSimulate($command, $node, $deployment);

        $username = $node->hasOption('username') ? $node->getOption('username') . '@' : '';
        $hostname = $node->getHostname();
        $noPubkeyAuthentication = $node->hasOption('password') ? ' -o PubkeyAuthentication=no' : '';
        $port = $node->hasOption('port') ? ' -p ' . escapeshellarg($node->getOption('port')) : '';
        $key = $node->hasOption('privateKeyFile') ? ' -i ' . escapeshellarg($node->getOption('privateKeyFile')) : '';
        $rshFlag = ($node->isLocalhost() ? '' : '--rsh="ssh' . $noPubkeyAuthentication . $port . $key . '" ');

        $rsyncFlags = $options['rsyncFlags'] . $this->getExcludeFlags($options['rsyncExcludes']);

        $destinationArgument = ($node->isLocalhost() ? $remotePath : "{$username}{$hostname}:{$remotePath}");

        $command = "rsync {$options['quietFlag']} --compress {$rshFlag} {$rsyncFlags} " . escapeshellarg($localPackagePath . '/.') . ' ' . escapeshellarg($destinationArgument);

        if ($node->hasOption('password')) {
            $passwordSshLoginScriptPathAndFilename = Files::concatenatePaths([dirname(__DIR__, 3), 'Resources', 'Private/Scripts/PasswordSshLogin.expect']);
            if (Phar::running() !== '') {
                $passwordSshLoginScriptContents = file_get_contents($passwordSshLoginScriptPathAndFilename);
                $passwordSshLoginScriptPathAndFilename = Files::concatenatePaths([$deployment->getTemporaryPath(), 'PasswordSshLogin.expect']);
                file_put_contents($passwordSshLoginScriptPathAndFilename, $passwordSshLoginScriptContents);
            }
            $command = sprintf('expect %s %s %s', escapeshellarg($passwordSshLoginScriptPathAndFilename), escapeshellarg($node->getOption('password')), $command);
        }

        $localhost = new Node('localhost');
        $localhost->onLocalhost();
        $this->shell->executeOrSimulate($command, $localhost, $deployment);

        if (isset($passwordSshLoginScriptPathAndFilename) && Phar::running() !== '') {
            unlink($passwordSshLoginScriptPathAndFilename);
        }

        $command = strtr("cp -RPp $remotePath/. $releasePath", "\t\n", '  ');
        // TODO Copy revision file (if it exists) for application to deployment path with release identifier

        $this->shell->executeOrSimulate($command, $node, $deployment);
    }

    /**
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    public function rollback(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $releasePath = $deployment->getApplicationReleasePath($node);
        $this->shell->execute('rm -Rf ' . $releasePath, $node, $deployment, true);
    }

    /**
     * Generates the --exclude flags for a given array of exclude patterns
     *
     * Example: ['foo', '/bar'] => --exclude 'foo' --exclude '/bar'
     */
    protected function getExcludeFlags(array $rsyncExcludes): string
    {
        return array_reduce($rsyncExcludes, function ($excludeOptions, $pattern) {
            if (!empty($this->replacePaths)) {
                $pattern = str_replace(array_keys($this->replacePaths), $this->replacePaths, $pattern);
            }
            return $excludeOptions . ' --exclude ' . escapeshellarg($pattern);
        }, '');
    }

    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('webDirectory', null);
        $resolver->setDefault('rsyncExcludes', ['.git']);
        $resolver->setDefault('verbose', false);
        $resolver->setDefault('quietFlag', static function (Options $options) {
            if ($options['verbose']) {
                return '';
            }

            return '-q';
        });
        $resolver->setDefault('rsyncFlags', '--recursive --times --perms --links --delete --delete-excluded');
    }
}
