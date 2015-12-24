<?php
namespace TYPO3\Surf\Task\TYPO3\Flow;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Surf\Application\TYPO3\Flow as FlowApplication;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandService;
use TYPO3\Surf\Exception\InvalidConfigurationException;

/**
 * A task to clear a list of Flow Framework cache
 *
 * You can configure the list of cache identifiers in the task option ```flushCacheList```, like::
 *
 *     $workflow->setTaskOptions('typo3.surf:typo3:flow:flushcachelist', [
 *         'flushCacheList' => 'TYPO3_TypoScript_Content, Flow_Session_MetaData, Flow_Session_Storage'
 *     ])
 *
 */
class FlushCacheListTask extends Task {

	/**
	 * @Flow\Inject
	 * @var ShellCommandService
	 */
	protected $shell;

	/**
	 * Execute this task
	 *
	 * @param Node $node
	 * @param Application $application
	 * @param Deployment $deployment
	 * @param array $options
	 * @return void
	 * @throws InvalidConfigurationException
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		if (!$application instanceof FlowApplication) {
			throw new InvalidConfigurationException(sprintf('Flow application needed for MigrateTask, got "%s"', get_class($application)), 1429774224);
		}

		if (!isset($options['flushCacheList']) || trim($options['flushCacheList']) === '') {
			throw new InvalidConfigurationException('Missing option "flushCacheList" for FlushCacheListTask', 1429774229);
		}

		if ($application->getVersion() >= '2.3') {
			$caches = is_array($options['flushCacheList']) ? $options['flushCacheList'] : explode(',', $options['flushCacheList']);
			$targetPath = $deployment->getApplicationReleasePath($application);
			foreach ($caches as $cache) {
				$deployment->getLogger()->log(sprintf('Flush cache with identifier "%s"', $cache));
				$this->shell->executeOrSimulate('cd ' . $targetPath . ' && ' . 'FLOW_CONTEXT=' . $application->getContext() . ' ./' . $application->getFlowScriptName() . ' ' . sprintf('typo3.flow:cache:flushone --identifier %s', $cache), $node, $deployment);
			}
		} else {
			throw new InvalidConfigurationException(sprintf('FlushCacheListTask is available since Flow Framework 2.3, your application version is "%s"', $application->getVersion()), 1434126060);
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
	 * Rollback the task
	 *
	 * @param Node $node
	 * @param Application $application
	 * @param Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function rollback(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		// Unable to rollback a clear cache command
	}

}