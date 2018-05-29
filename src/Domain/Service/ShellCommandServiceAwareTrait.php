<?php
namespace TYPO3\Surf\Domain\Service;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
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
     */
    public function setShellCommandService(ShellCommandService $shellCommandService)
    {
        $this->shell = $shellCommandService;
    }
}
