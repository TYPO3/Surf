<?php
namespace TYPO3\Deploy\Task;

/*                                                                        *
 * This script belongs to the FLOW3 package "TYPO3.Deploy".               *
 *                                                                        *
 *                                                                        */

use \TYPO3\Deploy\Domain\Model\Node;
use \TYPO3\Deploy\Domain\Model\Application;
use \TYPO3\Deploy\Domain\Model\Deployment;

/**
 * A generic checkout task
 *
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GitCheckoutTask extends \TYPO3\Deploy\Domain\Model\Task {

	/**
	 * @inject
	 * @var \TYPO3\Deploy\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Execute this task
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$releasePath = $deployment->getApplicationReleasePath($application);
		$deploymentPath = $application->getDeploymentPath();
		$repositoryUrl = $application->getOption('repositoryUrl');
		$sha1 = $this->shell->execute("git ls-remote $repositoryUrl master | awk '{print $1 }'", $node, $deployment, TRUE);
		if ($sha1 === FALSE) {
			throw new \Exception('Could not retrieve sha1 of git master');
		}

		$command = strtr("
			if [ -d $deploymentPath/cache/localgitclone ];
				then
					cd $deploymentPath/cache/localgitclone
					&& git fetch -q origin
					&& git reset -q --hard $sha1
					&& git submodule -q init
					&& for mod in `git submodule status | awk '{ print $2 }'`; do git config -f .git/config submodule.\${mod}.url `git config -f .gitmodules --get submodule.\${mod}.url` && echo synced \$mod; done
					&& git submodule -q sync
					&& git submodule -q update
					&& git clean -q -d -x -f; else git clone -q $repositoryUrl $deploymentPath/cache/localgitclone
					&& cd $deploymentPath/cache/localgitclone
					&& git checkout -q -b deploy $sha1
					&& git submodule -q init
					&& git submodule -q sync
					&& git submodule -q update;
				fi
		", "\t\n", "  ");

		$this->shell->executeOrSimulate($command, $node, $deployment);

		$command = strtr("
			cp -RPp $deploymentPath/cache/localgitclone/ $releasePath
				&& (echo $sha1 > $releasePath" . "REVISION)
			", "\t\n", "  ");

		$this->shell->executeOrSimulate($command, $node, $deployment);
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
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$releasePath = $deployment->getApplicationReleasePath($application);
		$this->shell->execute('rm -f ' . $releasePath . 'REVISION', $node, $deployment, TRUE);
	}

}
?>