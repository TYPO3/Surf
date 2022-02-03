<?php
namespace TYPO3\Surf\Domain\Model;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

/**
 * Representing a failed deployment
 *
 * This class does nothing
 */
class FailedDeployment extends Deployment
{
    /**
     * @param string $name
     */
    public function __construct($name = null)
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

    /**
     * Get the current deployment status
     *
     * @return int One of the Deployment::STATUS_* constants
     */
    public function getStatus()
    {
        return self::STATUS_UNKNOWN;
    }
}
