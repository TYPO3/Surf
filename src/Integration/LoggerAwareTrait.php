<?php

declare(strict_types=1);

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
