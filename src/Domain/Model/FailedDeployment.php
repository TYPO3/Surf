<?php
namespace TYPO3\Surf\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Enum\DeploymentStatus;

/**
 * Representing a failed deployment
 *
 * This class does nothing
 */
class FailedDeployment extends Deployment
{
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->releaseIdentifier = null;
    }

    /**
     * Initialize the deployment
     * noop
     */
    public function initialize(): void
    {
    }

    /**
     * Run this deployment
     * noop
     */
    public function deploy(): void
    {
    }

    /**
     * Simulate this deployment without executing tasks
     * noop
     */
    public function simulate(): void
    {
    }

    public function getStatus(): DeploymentStatus
    {
        return DeploymentStatus::UNKNOWN();
    }
}
