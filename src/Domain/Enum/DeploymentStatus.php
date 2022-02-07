<?php
declare(strict_types = 1);

namespace TYPO3\Surf\Domain\Enum;

use MyCLabs\Enum\Enum;

/**
 * @extends Enum<DeploymentStatus>
 *
 * @method static DeploymentStatus SUCCESS()
 * @method static DeploymentStatus FAILED()
 * @method static DeploymentStatus CANCELLED()
 * @method static DeploymentStatus UNKNOWN()
 */
final class DeploymentStatus extends Enum
{
    /**
     * @var int
     */
    public const SUCCESS = 0;

    /**
     * @var int
     */
    public const FAILED = 1;

    /**
     * @var int
     */
    public const CANCELLED = 2;

    /**
     * @var int
     */
    public const UNKNOWN = 3;

    public function isUnknown(): bool
    {
        return $this->equals(self::UNKNOWN());
    }

    public function toInt(): int
    {
        if ($this->isFailed()) {
            return self::FAILED;
        }

        if ($this->isSuccess()) {
            return self::SUCCESS;
        }

        if ($this->isCancelled()) {
            return self::CANCELLED;
        }

        return self::UNKNOWN;
    }

    private function isSuccess(): bool
    {
        return $this->equals(self::SUCCESS());
    }

    private function isFailed(): bool
    {
        return $this->equals(self::FAILED());
    }

    private function isCancelled(): bool
    {
        return $this->equals(self::CANCELLED());
    }
}
