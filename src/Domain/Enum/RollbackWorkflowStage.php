<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

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
