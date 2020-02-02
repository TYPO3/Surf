<?php

namespace TYPO3\Surf\Task\Release;

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
 * Task for adding a "TYPO3.Release" download
 */
class AddDownloadTask extends Task implements ShellCommandServiceAwareInterface
{
    use ShellCommandServiceAwareTrait;

    /**
     * Execute this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function execute(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $options = $this->configureOptions($options);

        $host = $options['releaseHost'];
        $login = $options['releaseHostLogin'];
        $sitePath = $options['releaseHostSitePath'];
        $version = $options['version'];
        $label = $options['label'];
        $uriPattern = $options['downloadUriPattern'];
        $productName = $options['productName'];

        $downloads = array_map(static function ($file) use ($uriPattern) {
            return sprintf('"%s,%s,%s"', basename($file), sha1($file), sprintf($uriPattern, basename($file)));
        }, $options['files']);

        $this->shell->executeOrSimulate(sprintf(
            'ssh %s%s "cd \"%s\" ; ./flow release:adddownload --product-name \"%s\" --version \"%s\" --label \"%s\" %s"',
            ($login ? $login . '@' : ''),
            $host,
            $sitePath,
            $productName,
            $version,
            $label,
            implode(' ', $downloads)
        ), $node, $deployment);
    }

    /**
     * Simulate this task
     *
     * @param Node $node
     * @param Application $application
     * @param Deployment $deployment
     * @param array $options
     */
    public function simulate(Node $node, Application $application, Deployment $deployment, array $options = [])
    {
        $this->execute($node, $application, $deployment, $options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function resolveOptions(OptionsResolver $resolver)
    {
        $resolver->setAllowedTypes('files', 'array');
        $resolver->setRequired(['releaseHost', 'releaseHostSitePath', 'version', 'label', 'downloadUriPattern', 'productName', 'files']);
    }
}
