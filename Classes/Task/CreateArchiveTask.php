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
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CreateArchiveTask extends \TYPO3\Deploy\Domain\Model\Task {

	/**
	 * @inject
	 * @var \TYPO3\Deploy\Domain\Service\ShellCommandService
	 */
	protected $shell;

	/**
	 * Executes this task
	 *
	 * @param \TYPO3\Deploy\Domain\Model\Node $node
	 * @param \TYPO3\Deploy\Domain\Model\Application $application
	 * @param \TYPO3\Deploy\Domain\Model\Deployment $deployment
	 * @param array $options
	 * @return void
	 */
	public function execute(Node $node, Application $application, Deployment $deployment, array $options = array()) {
		$this->checkOptionsForValidity($options);

		$this->shell->execute('rm -f ' . $options['targetFile'] . '; mkdir -p ' . dirname($options['targetFile']), $node, $deployment);
		$targetPath = $deployment->getApplicationReleasePath($application);

		$tarOptions = sprintf(' --transform="s,^./,%s/," ', $options['baseDirectory']);
		if (isset($options['exclude']) && is_array($options['exclude'])) {
			foreach ($options['exclude'] as $excludePattern) {
				$tarOptions .= sprintf(' --exclude="%s" ', $excludePattern);
			}
		}

		if (substr($options['targetFile'], -7) === '.tar.gz') {
			$tarOptions .= sprintf(' -czf %s --directory %s .', $options['targetFile'], $targetPath);
			$this->shell->execute(sprintf('tar %s || gnutar %s', $tarOptions, $tarOptions), $node, $deployment);

		} elseif (substr($options['targetFile'], -8) === '.tar.bz2') {

			$tarOptions .= sprintf(' -cjf %s --directory %s .', $options['targetFile'], $targetPath);
			$this->shell->execute(sprintf('tar %s || gnutar %s', $tarOptions, $tarOptions), $node, $deployment);

		} elseif (substr($options['targetFile'], -4) === '.zip') {

			$temporaryDirectory = sys_get_temp_dir() . '/' . uniqid('f3_deploy');
			$this->shell->execute(sprintf('mkdir -p %s', $temporaryDirectory), $node, $deployment);
			$tarOptions .= sprintf(' -cf %s/out.tar --directory %s . ', $temporaryDirectory, $targetPath, $options['baseDirectory']);
			$this->shell->execute(sprintf('tar %s || gnutar %s', $tarOptions, $tarOptions), $node, $deployment);
			$this->shell->execute(sprintf('cd %s; tar -xf out.tar; rm out.tar; zip --quiet -9 -r out %s', $temporaryDirectory, $options['baseDirectory']), $node, $deployment);
			$this->shell->execute(sprintf('mv %s/out.zip %s; rm -Rf %s', $temporaryDirectory, $options['targetFile'], $temporaryDirectory), $node, $deployment);

		} else {
			throw new \Exception('Unknown target file format', 1314248387);
		}
	}

	protected function checkOptionsForValidity($options) {
		if (!isset($options['sourceDirectory']) || !is_dir($options['sourceDirectory'])) {
			throw new \Exception('sourceDirectory not configured', 1314187354);
		}

		if (!isset($options['targetFile'])) {
			throw new \Exception('targetFile not configured', 1314187356);
		}
		if (!preg_match('/\.(tar\.gz|tar\.bz2|zip)$/', $options['targetFile'])) {
			throw new \Exception('targetFile only with file ending tar.gz, tar.bz2 or zip supported, given: "' . $options['targetFile'] . '"!', 1314187359);
		}

		if (!isset($options['baseDirectory'])) {
			throw new \Exception('baseDirectory not configured', 1314187361);
		}
	}

}
?>
