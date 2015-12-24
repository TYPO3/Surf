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
 * Task for adding a "TYPO3.Release" download
 *
 */
class AddDownloadTask extends \TYPO3\Surf\Domain\Model\Task {

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
		$sitePath =  $options['releaseHostSitePath'];
		$version = $options['version'];
		$label = $options['label'];
		$uriPattern = $options['downloadUriPattern'];
		$productName = $options['productName'];
		$files = $options['files'];

		$downloads = array();
		foreach ($files as $file) {
			$downloads[] = sprintf('"%s,%s,%s"', basename($file), sha1($file), sprintf($uriPattern, basename($file)));
		}
		$this->shell->executeOrSimulate(sprintf('ssh %s%s "cd \"%s\" ; ./flow release:adddownload --product-name \"%s\" --version \"%s\" --label \"%s\" %s"', ($login ? $login . '@' : ''), $host, $sitePath, $productName, $version, $label, implode(' ', $downloads)), $node, $deployment);
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
	 * Check if all required options are given
	 *
	 * @param array $options
	 * @return void
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 */
	protected function checkOptionsForValidity(array $options) {
		foreach (array('releaseHost', 'releaseHostSitePath', 'version', 'label', 'downloadUriPattern', 'productName', 'files') as $optionName) {
			if (!isset($options[$optionName])) {
				throw new \TYPO3\Surf\Exception\InvalidConfigurationException('"' . $optionName . '" option not set', 1321549657);
			}
		}
	}
}
?>