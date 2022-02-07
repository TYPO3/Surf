<?php
declare(strict_types = 1);

namespace TYPO3\Surf\Domain\Enum;

use MyCLabs\Enum\Enum;

/**
 * @extends Enum<SimpleWorkflowStage>
 */
final class SimpleWorkflowStage extends Enum
{
    // Initialize directories etc. (first time deploy)
    public const STEP_01_INITIALIZE = 'initialize';
    // Lock deployment
    public const STEP_02_LOCK = 'lock';
    // Local preparation of and packaging of application assets (code and files)
    public const STEP_03_PACKAGE = 'package';
    // Transfer of application assets to the node
    public const STEP_04_TRANSFER = 'transfer';
    // Update the application assets on the node
    public const STEP_05_UPDATE = 'update';
    // Migrate (Doctrine, custom)
    public const STEP_06_MIGRATE = 'migrate';
    // Prepare final release (e.g. warmup)
    public const STEP_07_FINALIZE = 'finalize';
    // Smoke test
    public const STEP_08_TEST = 'test';
    // Do symlink to current release
    public const STEP_09_SWITCH = 'switch';
    // Delete temporary files or previous releases
    public const STEP_10_CLEANUP = 'cleanup';
    // Unlock deployment
    public const STEP_11_UNLOCK = 'unlock';
}
