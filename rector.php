<?php
declare(strict_types = 1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters
        ->set(Option::AUTOLOAD_PATHS, [
            __DIR__ . '/vendor/autoload.php'
        ])
        ->set(Option::EXCLUDE_PATHS, [
            __DIR__ . '/.github',
            __DIR__ . '/Documentation',
            __DIR__ . '/vendor',
        ])
        ->set(Option::SETS, [
            SetList::CODE_QUALITY,
            SetList::PHP_53,
            SetList::PHP_54,
            SetList::PHP_55,
            SetList::PHP_56,
            SetList::PHP_70,
        ])
        ->set(Option::PHP_VERSION_FEATURES, '7.0');
};
