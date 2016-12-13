<?php


namespace TYPO3\Surf\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */

use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use Webmozart\Assert\Assert;

class ShellReplacePathCompositeService implements ShellReplacePathServiceInterface
{

    /**
     * @var ShellReplacePathServiceInterface[]
     */
    private $shellReplacePathServices;

    /**
     * ShellReplacePathCompositeService constructor.
     *
     * @param ShellReplacePathServiceInterface[] $shellReplacePathServices
     * @throws \InvalidArgumentException
     */
    public function __construct(array $shellReplacePathServices)
    {
        Assert::allIsInstanceOf($shellReplacePathServices, 'TYPO3\Surf\Domain\Service\ShellReplacePathServiceInterface');

        $this->shellReplacePathServices = $shellReplacePathServices;
    }

    /**
     * @param $command
     * @param Application $application
     * @param Deployment $deployment
     * @return string
     */
    public function replacePaths($command, Application $application, Deployment $deployment)
    {
        foreach ($this->shellReplacePathServices as $shellReplacePathService) {
            $command = $shellReplacePathService->replacePaths($command, $application, $deployment);
        }
        return $command;
    }


}