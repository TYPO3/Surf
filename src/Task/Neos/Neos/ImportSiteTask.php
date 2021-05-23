<?php
namespace TYPO3\Surf\Task\Neos\Neos;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\OptionsResolver\OptionsResolver;
use TYPO3\Surf\Application\Neos\Flow;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\Task;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareInterface;
use TYPO3\Surf\Domain\Service\ShellCommandServiceAwareTrait;
use Webmozart\Assert\Assert;

/**
 * This task imports a given site into a neos project by running site:import
 *
 * This command expects a Sites.xml file to be located in the private resources directory of the given package
 * (Resources/Private/Content/Sites.xml)
 *
 * It takes the following arguments:
 *
 * * sitePackageKey (required)
 *
 * Example:
 *  $workflow
 *      ->setTaskOptions(\TYPO3\Surf\Task\TYPO3\CMS\ImportSiteTask::class, [
 *              'sitePackageKey' => 'Neos.ExampleSite'
 *          ]
 *      );
 */
class ImportSiteTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        Assert::isInstanceOf($application, Flow::class, sprintf('Flow application needed for ImportSiteTask, got "%s"', get_class($application)));

        $options = $this->configureOptions($options);

        $targetPath = $deployment->getApplicationReleasePath($node);
        $arguments = [
            '--package-key',
            $options['sitePackageKey']
        ];
        $this->shell->executeOrSimulate($application->buildCommand($targetPath, 'site:import', $arguments), $node, $deployment);
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
        $resolver->setRequired('sitePackageKey');
    }
}
