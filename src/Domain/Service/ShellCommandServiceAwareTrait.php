<?php
namespace TYPO3\Surf\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf"                  *
 *                                                                        *
 *                                                                        */
/**
 * A shell command service aware class
 */
trait ShellCommandServiceAwareTrait
{
    /**
     * @var ShellCommandService
     */
    protected $shell;

    /**
     * @param ShellCommandService $shellCommandService
     * @return null
     */
    public function setShellCommandService(ShellCommandService $shellCommandService)
    {
        $this->shell = $shellCommandService;
    }
}
