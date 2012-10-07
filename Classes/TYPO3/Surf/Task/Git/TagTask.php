<?php
namespace TYPO3\Surf\Task\Git;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Exception\InvalidConfigurationException;

use TYPO3\Flow\Annotations as Flow;

/**
 * A task which can be used to tag a git repository and its submodules
 *
 */
class TagTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Execute this task
	 *
	 * Options:
	 *   tagName: The tag name to use
	 *   description: The description for the tag
	 *   recurseIntoSubmodules: If true, tag submodules as well (optional)
	 *   submoduleTagNamePrefix: Prefix for the submodule tags (optional)
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		if (!isset($options['tagName'])) {
			throw new InvalidConfigurationException('Missing "tagName" option for TagTask', 1314186541);
		}

		if (!isset($options['description'])) {
			throw new InvalidConfigurationException('Missing "description" option for TagTask', 1314186553);
		}

		if (!isset($options['submoduleTagNamePrefix'])) {
			$options['submoduleTagNamePrefix'] = '';
		}

		$targetPath = $deployment->getApplicationReleasePath($application);
		$this->shell->executeOrSimulate(sprintf('cd ' . $targetPath . '; git tag -f -a -m "%s" %s', $options['description'], $options['tagName']), $node, $deployment);
		if (isset($options['recurseIntoSubmodules']) && $options['recurseIntoSubmodules'] === TRUE) {
			$this->shell->executeOrSimulate(sprintf('cd ' . $targetPath . '; git submodule foreach \'git tag -f -a -m "%s" %s\'', $options['description'], $options['submoduleTagNamePrefix'] . $options['tagName']), $node, $deployment);
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

}
?>