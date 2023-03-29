<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Integration;

use Psr\Log\LoggerInterface;

trait LoggerAwareTrait
{
    protected LoggerInterface $logger;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
