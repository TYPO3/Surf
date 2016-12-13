<?php


namespace TYPO3\Surf\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;

class LocalShellReplacePathService implements ShellReplacePathInterface
{

    /**
     * @var ShellReplacePathInterface
     */
    private $shellReplacePathService;

    /**
     * LocalShellReplacePathService constructor.
     *
     * @param ShellReplacePathInterface|null $shellReplacePathService
     */
    public function __construct(ShellReplacePathInterface $shellReplacePathService = null)
    {
        if(null === $shellReplacePathService)
        {
            $shellReplacePathService = new ShellReplacePathService();
        }
        $this->shellReplacePathService = $shellReplacePathService;
    }

    /**
     * @param $command
     * @param Application $application
     * @param Deployment $deployment
     *
     * @return mixed
     */
    public function replacePaths($command, Application $application, Deployment $deployment)
    {
        $replacePaths = array(
            '{workspacePath}' => escapeshellarg($deployment->getWorkspacePath($application))
        );
        $command = str_replace(array_keys($replacePaths), $replacePaths, $command);

        return $this->shellReplacePathService->replacePaths($command, $application, $deployment);
    }


}