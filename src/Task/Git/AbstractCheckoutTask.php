<?php
namespace TYPO3\Surf\Task\Git;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Surf".            *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * An abstract git checkout task
 *
 */
abstract class AbstractCheckoutTask extends \TYPO3\Surf\Domain\Model\Task
{

    /**
     * @Flow\Inject
     * @var \TYPO3\Surf\Domain\Service\ShellCommandService
     */
    protected $shell;

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     * @return void
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = array())
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return array
     * @throws \TYPO3\Surf\Exception\TaskExecutionException
     */
    protected function resolveSha1(Node $node, Deployment $deployment, array $options)
    {
        if (isset($options['sha1'])) {
            $sha1 = $options['sha1'];
            if (preg_match('/[a-f0-9]{40}/', $sha1) === 0) {
                throw new TaskExecutionException('The given sha1  "' . $options['sha1'] . '" is invalid', 1335974900);
            }
        } else {
            if (isset($options['tag'])) {
                $sha1 = $this->shell->execute("git ls-remote {$options['repositoryUrl']} refs/tags/{$options['tag']} | awk '{print $1 }'", $node, $deployment, true);
                if (preg_match('/[a-f0-9]{40}/', $sha1) === 0) {
                    throw new TaskExecutionException('Could not retrieve sha1 of git tag "' . $options['tag'] . '"', 1335974915);
                }
            } else {
                $branch = 'master';
                if (isset($options['branch'])) {
                    $branch = $options['branch'];
                }
                $sha1 = $this->shell->execute("git ls-remote {$options['repositoryUrl']} refs/heads/$branch | awk '{print $1 }'", $node, $deployment, true);
                if (preg_match('/^[a-f0-9]{40}$/', $sha1) === 0) {
                    throw new TaskExecutionException('Could not retrieve sha1 of git branch "' . $branch . '"', 1335974926);
                }
            }
        }
        return $sha1;
    }

    /**
     * @param string $checkoutPath
     * @param \TYPO3\Surf\Domain\Model\Node $node
     * @param \TYPO3\Surf\Domain\Model\Deployment $deployment
     * @param array $options
     * @return array
     */
    protected function executeOrSimulateGitCloneOrUpdate($checkoutPath, Node $node, Deployment $deployment, array $options)
    {
        $sha1 = $this->resolveSha1($node, $deployment, $options);
        $repositoryUrl = escapeshellarg($options['repositoryUrl']);
        $quietFlag = (isset($options['verbose']) && $options['verbose']) ? '' : '-q';
        $recursiveFlag = (isset($options['recursiveSubmodules']) && ! $options['recursiveSubmodules']) ? '' : '--recursive';
        $checkoutPath = escapeshellarg($checkoutPath);
        $command = strtr("
			if [ -d $checkoutPath ];
				then
					cd $checkoutPath
					&& git remote set-url origin $repositoryUrl
					&& git fetch $quietFlag origin
					" . (isset($options['fetchAllTags']) && $options['fetchAllTags'] === true ? '&& git fetch --tags' : '') . "
					&& git reset $quietFlag --hard $sha1
					&& git submodule $quietFlag init
					&& for mod in `git submodule status | awk '{ print $2 }'`; do git config -f .git/config submodule.\${mod}.url `git config -f .gitmodules --get submodule.\${mod}.url` && echo synced \$mod; done
					&& git submodule $quietFlag sync
					&& git submodule $quietFlag update --init $recursiveFlag
					" . (isset($options['hardClean']) && $options['hardClean'] === true ? "&& git clean $quietFlag -d -x -ff" : '') . ";
				else
					git clone $quietFlag $repositoryUrl $checkoutPath
					&& cd $checkoutPath
					" . (isset($options['fetchAllTags']) && $options['fetchAllTags'] === true ? '&& git fetch --tags' : '') . "
					&& git checkout $quietFlag -b deploy $sha1
					&& git submodule $quietFlag init
					&& git submodule $quietFlag sync
					&& git submodule $quietFlag update --init $recursiveFlag;
			fi
		", "\t\n", '  ');

        $this->shell->executeOrSimulate($command, $node, $deployment);

        return $sha1;
    }

    /**
     * @param $gitPath
     * @param $sha1
     * @param Node $node
     * @param Deployment $deployment
     * @param array $options
     */
    protected function executeOrSimulatePostGitCheckoutCommands($gitPath, $sha1, Node $node, Deployment $deployment, array $options)
    {
        if (isset($options['gitPostCheckoutCommands'])) {
            $gitPostCheckoutCommands = $options['gitPostCheckoutCommands'];
            if (is_array($gitPostCheckoutCommands)) {
                foreach ($gitPostCheckoutCommands as $localPath => $postCheckoutCommandsPerPath) {
                    foreach ($postCheckoutCommandsPerPath as $postCheckoutCommand) {
                        $branchName = 'mybranch_' . trim($sha1) . '_' . uniqid();
                        $command = strtr("
							cd $gitPath
							&& cd $localPath
							&& git checkout -b $branchName
							&& $postCheckoutCommand
						", "\t\n", '  ');
                        $this->shell->executeOrSimulate($command, $node, $deployment);
                    }
                }
            }
        }
    }
}
