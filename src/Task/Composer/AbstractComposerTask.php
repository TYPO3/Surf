<?php
namespace TYPO3\Surf\Task\Composer;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Utility\Files;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;

/**
 * Installs the composer packages based on a composer.json file in the projects root folder
 */
abstract class AbstractComposerTask extends \TYPO3\Surf\Domain\Model\Task implements \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface
{
    use \TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

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
    protected $arguments = array();

    /**
     * Suffix for the command
     *
     * @var array
     */
    protected $suffix = array('2>&1');

    /**
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        if (isset($options['useApplicationWorkspace']) && $options['useApplicationWorkspace'] === true) {
            $composerRootPath = $deployment->getWorkspacePath($application);
        } else {
            $composerRootPath = $deployment->getApplicationReleasePath($application);
        }

        if (isset($options['nodeName'])) {
            $node = $deployment->getNode($options['nodeName']);
            if ($node === null) {
                throw new \TYPO3\Surf\Exception\InvalidConfigurationException(sprintf('Node "%s" not found', $options['nodeName']), 1369759412);
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
     * @return void
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * Build the composer command in the given $path.
     *
     * @param string $manifestPath
     * @param array $options
     * @return array
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     */
    protected function buildComposerCommands($manifestPath, array $options)
    {
        if (!isset($options['composerCommandPath'])) {
            throw new \TYPO3\Surf\Exception\TaskExecutionException('Composer command not found. Set the composerCommandPath option.', 1349163257);
        }

        if (isset($options['additionalArguments'])){
            $additionalArguments = is_array($options['additionalArguments']) ? $options['additionalArguments'] : array($options['additionalArguments']);
        } else {
            $additionalArguments = array();
        }

        $arguments = array_merge(
            array(escapeshellcmd($options['composerCommandPath']), $this->command),
            $this->arguments,
            array_map('escapeshellarg', $additionalArguments),
            $this->suffix
        );
        $script = implode(' ', $arguments);

        return array(
            'cd ' . escapeshellarg($manifestPath),
            $script,
        );
    }

    /**
     * Checks if a composer manifest exists in the directory at the given path.
     *
     * If no manifest exists, a log message is recorded.
     *
     * @param string $path
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @return bool
     */
    protected function composerManifestExists($path, Node $node, Deployment $deployment)
    {
        // In dry run mode, no checkout is there, this we must not assume a composer.json is there!
        if ($deployment->isDryRun()) {
            return false;
        }
        $composerJsonPath = Files::concatenatePaths(array($path, 'composer.json'));
        $composerJsonExists = $this->shell->executeOrSimulate('test -f ' . escapeshellarg($composerJsonPath), $node, $deployment, true);
        if ($composerJsonExists === false) {
            $deployment->getLogger()->debug('No composer.json found in path "' . $composerJsonPath . '"');
            return false;
        }

        return true;
    }
}
