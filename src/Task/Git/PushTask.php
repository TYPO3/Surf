<?php
namespace TYPO3\Surf\Task\Git;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;

/**
 * A task which can push to a git remote
 *
 * It takes the following options:
 *
 * * remote - The git remote to use.
 * * refspec - The refspec to push.
 * * recurseIntoSubmodules (optional) - If true, push submodules as well.
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions('TYPO3\Surf\Task\Git\PushTask', [
 *              'remote' => 'git@github.com:TYPO3/Surf.git',
 *              'refspec' => 'master',
 *              'recurseIntoSubmodules' => true
 *          ]
 *      );
 */
class PushTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);

        $targetPath = $deployment->getApplicationReleasePath($node);

        $this->shell->executeOrSimulate(sprintf('cd ' . $targetPath . '; git push -f %s %s', $options['remote'], $options['refspec']), $node, $deployment);
        if ($options['recurseIntoSubmodules']) {
            $this->shell->executeOrSimulate(sprintf('cd ' . $targetPath . '; git submodule foreach \'git push -f %s %s\'', $options['remote'], $options['refspec']), $node, $deployment);
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['remote', 'refspec']);
        $resolver->setDefault('recurseIntoSubmodules', false);
        $resolver->setAllowedTypes('recurseIntoSubmodules', 'boolean');
    }
}
