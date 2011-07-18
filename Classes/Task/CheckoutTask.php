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
class CheckoutTask extends \TYPO3\Deploy\Domain\Model\Task {

	/**
	 * @inject
	 * @var \TYPO3\Deploy\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Execute this task
	 *
	 * @param $node
	 * @param $application
	 * @param $deployment
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, $options = array()) {
		$releasePath = $deployment->getApplicationReleasePath($application);
		$baseDeploymentPath = $application->getOption('deploymentPath');
		$repositoryUrl = $application->getOption('repositoryUrl');
		$sha1 = $this->shell->execute("git ls-remote $repositoryUrl master | awk '{print $1 }'", $node, $deployment);
		if ($sha1 === FALSE) {
			throw new \Exception('Could not retrieve sha1 of git master');
		}

		$command = strtr("
			if [ -d $baseDeploymentPath/cache/localgitclone ];
				then
					cd $baseDeploymentPath/cache/localgitclone
					&& git fetch -q origin
					&& git reset -q --hard $sha1
					&& git submodule -q init
					&& for mod in `git submodule status | awk '{ print $2 }'`; do git config -f .git/config submodule.\${mod}.url `git config -f .gitmodules --get submodule.\${mod}.url` && echo synced \$mod; done
					&& git submodule -q sync
					&& git submodule -q update
					&& git clean -q -d -x -f; else git clone -q $repositoryUrl $baseDeploymentPath/cache/localgitclone
					&& cd $baseDeploymentPath/cache/localgitclone
					&& git checkout -q -b deploy $sha1
					&& git submodule -q init
					&& git submodule -q sync
					&& git submodule -q update;
				fi
		", "\t\n", "  ");

		$this->shell->execute($command, $node, $deployment, TRUE);

		$command = strtr("
			cp -RPp $baseDeploymentPath/cache/localgitclone/ $releasePath
				&& (echo $sha1 > $releasePath" . "REVISION)
			", "\t\n", "  ");

		$this->shell->execute($command, $node, $deployment, TRUE);
	}

}
?>