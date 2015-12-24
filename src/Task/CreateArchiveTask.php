<?php
namespace TYPO3\Surf\Task;

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
 * A task for creating an zip / tar.gz / tar.bz2 archive.
 * Needs the following options:
 *
 * - sourceDirectory -- directory which should be compressed
 * - targetFile -- target file. The file ending defines the format. Supported are .zip, .tar.gz, .tar.bz2
 * - baseDirectory -- base directory in the compressed archive in which all files should reside in.
 * - exclude -- an array of exclude patterns, as being understood by tar.
 *
 * This task needs the following unix command line tools:
 * - tar / gnutar
 * - zip
 *
 */
class CreateArchiveTask extends \TYPO3\Surf\Domain\Model\Task {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Surf\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Executes this task
	 *
	 * @param \TYPO3\Surf\Domain\Model\Node $node
	 * @param \TYPO3\Surf\Domain\Model\Application $application
	 * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 * @throws \TYPO3\Surf\Exception\TaskExecutionException
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$this->checkOptionsForValidity($options);

		$this->shell->execute('rm -f ' . $options['targetFile'] . '; mkdir -p ' . dirname($options['targetFile']), $node, $deployment);
		$sourcePath = $deployment->getApplicationReleasePath($application);

		$tarOptions = sprintf(' --transform="s,^%s,%s," ', ltrim($sourcePath, '/'), $options['baseDirectory']);
		if (isset($options['exclude']) && is_array($options['exclude'])) {
			foreach ($options['exclude'] as $excludePattern) {
				$tarOptions .= sprintf(' --exclude="%s" ', $excludePattern);
			}
		}

		if (substr($options['targetFile'], -7) === '.tar.gz') {
			$tarOptions .= sprintf(' -czf %s %s', $options['targetFile'], $sourcePath);
			$this->shell->execute(sprintf('tar %s || gnutar %s', $tarOptions, $tarOptions), $node, $deployment);

		} elseif (substr($options['targetFile'], -8) === '.tar.bz2') {

			$tarOptions .= sprintf(' -cjf %s %s', $options['targetFile'], $sourcePath);
			$this->shell->execute(sprintf('tar %s || gnutar %s', $tarOptions, $tarOptions), $node, $deployment);

		} elseif (substr($options['targetFile'], -4) === '.zip') {

			$temporaryDirectory = sys_get_temp_dir() . '/' . uniqid('f3_deploy');
			$this->shell->execute(sprintf('mkdir -p %s', $temporaryDirectory), $node, $deployment);
			$tarOptions .= sprintf(' -cf %s/out.tar %s', $temporaryDirectory, $sourcePath);
			$this->shell->execute(sprintf('tar %s || gnutar %s', $tarOptions, $tarOptions), $node, $deployment);
			$this->shell->execute(sprintf('cd %s; tar -xf out.tar; rm out.tar; zip --quiet -9 -r out %s', $temporaryDirectory, $options['baseDirectory']), $node, $deployment);
			$this->shell->execute(sprintf('mv %s/out.zip %s; rm -Rf %s', $temporaryDirectory, $options['targetFile'], $temporaryDirectory), $node, $deployment);

		} else {
			throw new \TYPO3\Surf\Exception\TaskExecutionException('Unknown target file format', 1314248387);
		}
	}

	/**
	 * @param array $options
	 * @return void
	 * @throws \TYPO3\Surf\Exception\InvalidConfigurationException
	 */
	protected function checkOptionsForValidity(array $options) {
		if (!isset($options['sourceDirectory']) || !is_dir($options['sourceDirectory'])) {
			throw new InvalidConfigurationException('sourceDirectory not configured', 1314187354);
		}

		if (!isset($options['targetFile'])) {
			throw new InvalidConfigurationException('targetFile not configured', 1314187356);
		}
		if (!preg_match('/\.(tar\.gz|tar\.bz2|zip)$/', $options['targetFile'])) {
			throw new InvalidConfigurationException('targetFile only with file ending tar.gz, tar.bz2 or zip supported, given: "' . $options['targetFile'] . '"!', 1314187359);
		}

		if (!isset($options['baseDirectory'])) {
			throw new InvalidConfigurationException('baseDirectory not configured', 1314187361);
		}
	}

}
?>
