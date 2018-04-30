<?php
namespace TYPO3\Surf\Task\TYPO3\CMS;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * This task sets up extensions using typo3_console.
 * Set up means: database migration, files, database data.
 *
 * @param array $extensionKeys=array() Extension keys for extensions that should be set up. If empty, all active non core extensions will be set up.
 */
class SetUpExtensionsTask extends AbstractCliTask
{
    /**
     * Execute this task
     *
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Application $application
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
     * @return void
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->ensureApplicationIsTypo3Cms($application);
        try {
            $scriptFileName = $this->getConsoleScriptFileName($node, $application, $deployment, $options);
        } catch (InvalidConfigurationException $e) {
            $deployment->getLogger()->warning('TYPO3 Console script (' .$options['scriptFileName'] . ') was not found! Make sure it is available in your project, you set the "scriptFileName" option correctly or remove this task (' . __CLASS__ . ') in your deployment configuration!');
            return;
        }
        $extensionKeys = isset($options['extensionKeys']) ? $options['extensionKeys'] : array();
        $commandArguments = array($scriptFileName);
        if (empty($extensionKeys)) {
            $commandArguments[] = 'extension:setupactive';
        } else {
            $commandArguments[] = 'extension:setup';
            $commandArguments[] = implode(',', $extensionKeys);
        }
        $this->executeCliCommand(
            $commandArguments,
            $node,
            $application,
            $deployment,
            $options
        );
    }
}
