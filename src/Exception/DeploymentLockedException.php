<?php

namespace TYPO3\Surf\Exception;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Exception as SurfException;

final class DeploymentLockedException extends SurfException
{
    /**
     * @param Deployment $deployment
     * @param $currentDeploymentLockIdentifier
     *
     * @return DeploymentLockedException
     */
    public static function deploymentLockedBy(Deployment $deployment, $currentDeploymentLockIdentifier)
    {
        return new static(sprintf('Deployment %s is currently locked by %s. Use parameter --force to unlock and deploy', $deployment->getName(), $currentDeploymentLockIdentifier));
    }
}
