<?php
namespace TYPO3\Surf\Task;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Exception\TaskExecutionException;

use TYPO3\Flow\Annotations as Flow;

/**
 * A generic checkout task
 *
 */
class GitCheckoutTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Execute this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 * @throws \TYPO3\Surf\Exception\TaskExecutionException
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		if (!isset($options['repositoryUrl'])) {
			throw new \TYPO3\Surf\Exception\InvalidConfigurationException(sprintf('Missing "repositoryUrl" option for application "%s"', $application->getName()), 1335974764);
		}

		$repositoryUrl = $options['repositoryUrl'];
		$releasePath = $deployment->getApplicationReleasePath($application);
		$deploymentPath = $application->getDeploymentPath();

		if (isset($options['sha1'])) {
			$sha1 = $options['sha1'];
			if (preg_match('/[a-z0-9]{40}/', $sha1) === 0) {
				throw new TaskExecutionException('The given sha1  "' . $options['sha1'] . '" is invalid', 1335974900);
			}
		} else {
			if (isset($options['tag'])) {
				$sha1 = $this->shell->execute("git ls-remote $repositoryUrl refs/tags/{$options['tag']} | awk '{print $1 }'", $node, $deployment, TRUE);
				if (preg_match('/[a-z0-9]{40}/', $sha1) === 0) {
					throw new TaskExecutionException('Could not retrieve sha1 of git tag "' . $options['tag'] . '"', 1335974915);
				}
			} else {
				if (!isset($options['branch'])) {
					$options['branch'] = 'master';
				}
				$sha1 = $this->shell->execute("git ls-remote $repositoryUrl refs/heads/{$options['branch']} | awk '{print $1 }'", $node, $deployment, TRUE);
				if (preg_match('/^[a-z0-9]{40}$/', $sha1) === 0) {
					throw new TaskExecutionException('Could not retrieve sha1 of git branch "' . $options['branch'] . '"', 1335974926);
				}
			}
		}

		$quietFlag = (isset($options['verbose']) && $options['verbose']) ? '' : '-q';
		$command = strtr("
			if [ -d $deploymentPath/cache/localgitclone ];
				then
					cd $deploymentPath/cache/localgitclone
					&& git fetch $quietFlag origin
					&& git reset $quietFlag --hard $sha1
					&& git submodule $quietFlag init
					&& for mod in `git submodule status | awk '{ print $2 }'`; do git config -f .git/config submodule.\${mod}.url `git config -f .gitmodules --get submodule.\${mod}.url` && echo synced \$mod; done
					&& git submodule $quietFlag sync
					&& git submodule $quietFlag update --init --recursive
					&& git clean $quietFlag -d -x -ff;
				else
					git clone $quietFlag $repositoryUrl $deploymentPath/cache/localgitclone
					&& cd $deploymentPath/cache/localgitclone
					&& git checkout $quietFlag -b deploy $sha1
					&& git submodule $quietFlag init
					&& git submodule $quietFlag sync
					&& git submodule $quietFlag update --init --recursive;
			fi
		", "\t\n", "  ");

		$this->shell->executeOrSimulate($command, $node, $deployment);

		$command = strtr("
			cp -RPp $deploymentPath/cache/localgitclone/. $releasePath
				&& (echo $sha1 > $releasePath" . "REVISION)
			", "\t\n", "  ");

		$this->shell->executeOrSimulate($command, $node, $deployment);

		if (isset($options['gitPostCheckoutCommands'])) {
			$gitPostCheckoutCommands = $options['gitPostCheckoutCommands'];
			if (is_array($gitPostCheckoutCommands)) {
				foreach ($gitPostCheckoutCommands as $localPath => $postCheckoutCommandsPerPath) {
					foreach ($postCheckoutCommandsPerPath as $postCheckoutCommand) {
						$branchName = 'mybranch_' . trim($sha1) . '_' . uniqid();
						$command = strtr("
							cd $releasePath
							&& cd $localPath
							&& git checkout -b $branchName
							&& $postCheckoutCommand
						", "\t\n", "  ");
						$this->shell->executeOrSimulate($command, $node, $deployment);
					}
				}
			}
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
	 */
	public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$this->execute($node, $application, $deployment, $options);
	}

	/**
	 * Rollback this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$releasePath = $deployment->getApplicationReleasePath($application);
		$this->shell->execute('rm -f ' . $releasePath . 'REVISION', $node, $deployment, TRUE);
	}

}
?>