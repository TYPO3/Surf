<?php
namespace TYPO3\Surf\Task\Release;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

use TYPO3\Flow\Annotations as Flow;

/**
 * Task for doing a "TYPO3.Release" release
 *
 */
class ReleaseTask extends PrepareReleaseTask {

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
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$this->checkOptionsForValidity($options);
		$host = $options['releaseHost'];
		$login = $options['releaseHostLogin'];
		$changeLogUri = $options['changeLogUri'];
		$sitePath =  $options['releaseHostSitePath'];
		$version = $options['version'];
		$productName = $options['productName'];

		$this->shell->executeOrSimulate(sprintf('ssh %s%s "cd \"%s\" ; ./flow release:release --product-name \"%s\" --version \"%s\" --change-log-uri \"%s\""', ($login ? $login . '@' : ''), $host, $sitePath, $productName, $version, ($changeLogUri ? $changeLogUri : '')), $node, $deployment);
	}
}
?>