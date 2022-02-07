<?php
declare(strict_types = 1);

namespace TYPO3\Surf\Domain\Enum;

use MyCLabs\Enum\Enum;

/**
 * @extends Enum<RollbackWorkflowStage>
 */
final class RollbackWorkflowStage extends Enum
{
    /**
     * @var string
     */
    public const STEP_01_INITIALIZE = 'rollback:initialize';

    /**
     * @var string
     */
    public const STEP_02_EXECUTE = 'rollback:execute';

    /**
     * @var string
     */
    public const STEP_03_CLEANUP = 'rollback:cleanup';
}
