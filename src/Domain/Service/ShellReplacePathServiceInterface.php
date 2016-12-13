<?php


namespace TYPO3\Surf\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

interface ShellReplacePathServiceInterface
{

    /**
     * @param $command
     * @param Application $application
     * @param Deployment $deployment
     *
     * @return string
     */
    public function replacePaths($command, Application $application, Deployment $deployment);

}