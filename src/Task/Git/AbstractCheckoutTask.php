<?php

declare(strict_types=1);

namespace TYPO3\Surf\Task\Git;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use TYPO3\Surf\Exception\TaskExecutionException;

/**
 * An abstract git checkout task
 */
abstract class AbstractCheckoutTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = []): void
    {
        $this->execute($node, $application, $deployment, $options);
    }

    protected function resolveSha1(Node $node, Deployment $deployment, array $options): string
    {
        if (isset($options['sha1'])) {
            $sha1 = $options['sha1'];
            if (preg_match('/[a-f0-9]{40}/', $sha1) === 0) {
                throw new TaskExecutionException('The given sha1  "' . $options['sha1'] . '" is invalid', 1335974900);
            }
        } elseif (isset($options['tag'])) {
            $sha1 = $this->shell->execute(
                "git ls-remote --sort=version:refname {$options['repositoryUrl']} 'refs/tags/{$options['tag']}' "
                    . "| awk '{print $1 }' "
                    . '| tail --lines=1',
                $node,
                $deployment,
                true
            );
            if (preg_match('/[a-f0-9]{40}/', $sha1) === 0) {
                throw new TaskExecutionException('Could not retrieve sha1 of git tag "' . $options['tag'] . '"', 1335974915);
            }
        } else {
            $branch = $options['branch'] ?? 'master';
            $sha1 = $this->shell->execute("git ls-remote {$options['repositoryUrl']} refs/heads/$branch | awk '{print $1 }'", $node, $deployment, true);
            if (preg_match('/^[a-f0-9]{40}$/', $sha1) === 0) {
                throw new TaskExecutionException('Could not retrieve sha1 of git branch "' . $branch . '"', 1335974926);
            }
        }
        return $sha1;
    }

    protected function executeOrSimulateGitCloneOrUpdate(string $checkoutPath, Node $node, Deployment $deployment, array $options): string
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

    protected function executeOrSimulatePostGitCheckoutCommands(string $gitPath, string $sha1, Node $node, Deployment $deployment, array $options): void
    {
        if (!isset($options['gitPostCheckoutCommands'])) {
            return;
        }

        $gitPostCheckoutCommands = $options['gitPostCheckoutCommands'];
        if (!is_array($gitPostCheckoutCommands)) {
            return;
        }

        foreach ($gitPostCheckoutCommands as $localPath => $postCheckoutCommandsPerPath) {
            foreach ($postCheckoutCommandsPerPath as $postCheckoutCommand) {
                $branchName = 'mybranch_' . trim($sha1) . '_' . uniqid('', true);
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
