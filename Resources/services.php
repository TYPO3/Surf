<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\Surf\Cli\Symfony\ConsoleApplication;
use TYPO3\Surf\Cli\Symfony\ConsoleOutputFactory;
use TYPO3\Surf\Cli\Symfony\Logger\ConsoleHandler;
use TYPO3\Surf\Cli\Symfony\Logger\LoggerFactory;
use TYPO3\Surf\Domain\Filesystem\FilesystemInterface;
use TYPO3\Surf\Domain\Model\RollbackWorkflow;
use TYPO3\Surf\Domain\Model\SimpleWorkflow;
use TYPO3\Surf\Domain\Version\ComposerVersionChecker;
use TYPO3\Surf\Domain\Version\VersionCheckerInterface;
use TYPO3\Surf\Integration\Factory;
use TYPO3\Surf\Integration\FactoryInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('name', 'TYPO3 Surf');

    $parameters->set('version', '3.4.3');

    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->public()
        ->bind('$name', '%name%')
        ->bind('$version', '%version%');

    $services->set(Client::class);

    $services->alias(ClientInterface::class, Client::class);

    $services->set(LoggerInterface::class)
        ->factory([service(LoggerFactory::class), 'createLogger']);

    $services->set(Application::class);

    $services->set(OutputInterface::class)
        ->factory([service(ConsoleOutputFactory::class), 'createOutput']);

    $services->load('TYPO3\Surf\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/{Cli,Application,Exception,Domain/Model,Domain/Enum,DeprecationMessageFactory.php,Exception.php,functions.php}']);

    $services->set(ConsoleApplication::class);

    $services->set(ConsoleOutputFactory::class);

    $services->set(ConsoleHandler::class);

    $services->set(LoggerFactory::class);

    $services->set(RollbackWorkflow::class)
        ->share(false);

    $services->set(SimpleWorkflow::class)
        ->share(false);

    $services->set(Factory::class)
        ->args([service(FilesystemInterface::class), service(LoggerInterface::class)]);

    $services->alias(FactoryInterface::class, Factory::class);

    $services->alias(VersionCheckerInterface::class, ComposerVersionChecker::class);

    $services->alias(PsrContainerInterface::class, 'service_container');
    $services->alias(ContainerInterface::class, 'service_container');
};
