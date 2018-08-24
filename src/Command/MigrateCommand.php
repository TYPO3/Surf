<?php
namespace TYPO3\Surf\Command;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Integration\FactoryAwareInterface;
use TYPO3\Surf\Integration\FactoryAwareTrait;

/**
 * Migrate old deployment definitions to new Surf version
 */
class MigrateCommand extends Command implements FactoryAwareInterface
{
    use FactoryAwareTrait;

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('migrate')
            ->setDescription('Migrates old deployment definitions to new Surf version')
            ->addArgument(
                'deploymentName',
                InputArgument::REQUIRED,
                'The deployment name to migrate'
            )
            ->addOption(
                'configurationPath',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path for deployment configuration files'
            );
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configurationPath = $input->getOption('configurationPath');
        $deploymentName = $input->getArgument('deploymentName');
        $basePath = $this->factory->getDeploymentsBasePath($configurationPath);

        $deploymentFileName = $basePath . '/' . $deploymentName . '.php';

        $legacyMap = require __DIR__ . '/../../Migrations/Code/LegacyClassMap.php';
        $fileContent = file_get_contents($deploymentFileName);
        foreach ($legacyMap as $identifier => $className) {
            if ($fileContent !== str_ireplace($identifier, str_replace('\\', '\\\\', $className), $fileContent)) {
                $output->writeln(sprintf('<warning>Legacy deployment task name or task option "%s" found!</warning>', $identifier));
                $fileContent = str_ireplace($identifier, str_replace('\\', '\\\\', $className), $fileContent);
            }
        }
        file_put_contents($deploymentFileName, $fileContent);
        $output->writeln('<info>Migrated deployment definition "' . $deploymentName . '"</info>');
    }
}
