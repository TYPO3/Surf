<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->importNames();

    // get parameters
    $parameters = $rectorConfig->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);
    $parameters->set(Option::SKIP, [
        AddLiteralSeparatorToNumberRector::class
    ]);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_CODE_QUALITY);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_SPECIFIC_METHOD);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_EXCEPTION);
    $rectorConfig->import(PHPUnitSetList::PHPUNIT_YIELD_DATA_PROVIDER);
    $rectorConfig->import(LevelSetList::UP_TO_PHP_74);

    // get services (needed for register a single rule)
    $services = $rectorConfig->services();

    // register a single rule
    //$services->set(TypedPropertyRector::class);
    //$services->set(AddVoidReturnTypeWhereNoReturnRector::class);
};
