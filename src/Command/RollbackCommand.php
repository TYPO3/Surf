<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace TYPO3\Surf\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Integration\FactoryInterface;

class RollbackCommand extends Command
{
    private FactoryInterface $factory;

    /**
     * @var string
     */
    protected static $defaultName = 'rollback';

    public function __construct(FactoryInterface $factory)
    {
        parent::__construct();
        $this->factory = $factory;
    }

    public static function getDefaultName(): ?string
    {
        return 'rollback';
    }

    protected function configure(): void
    {
        $this->setDescription('Rollback current to previous release and remove current folder')
            ->addArgument(
                'deploymentName',
                InputArgument::OPTIONAL,
                'The deployment name'
            )
            ->addOption(
                'configurationPath',
                null,
                InputOption::VALUE_OPTIONAL,
                'Path for deployment configuration files'
            )->addOption(
                'simulate',
                null,
                InputOption::VALUE_NONE,
                'Simulate rollback'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configurationPath = (string)$input->getOption('configurationPath');
        $deploymentName = (string)$input->getArgument('deploymentName');
        $simulate = (bool)$input->getOption('simulate');

        $deployment = $this->factory->getDeployment($deploymentName, $configurationPath, $simulate, false);
        $deployment->rollback($simulate);

        return $deployment->getStatus()->toInt();
    }
}
