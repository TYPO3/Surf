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
use TYPO3\Surf\Integration\FactoryInterface;

class RollbackCommand extends Command
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    public function __construct(FactoryInterface $factory, string $name = null)
    {
        parent::__construct($name);
        $this->factory = $factory;
    }

    protected function configure(): void
    {
        $this->setName('rollback')
            ->setDescription('Rollback current to previous release and remove current folder')
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
        $configurationPath = $input->getOption('configurationPath');
        $deploymentName = $input->getArgument('deploymentName');
        $deployment = $this->factory->getDeployment((string)$deploymentName, $configurationPath, $input->getOption('simulate'), false);
        $deployment->rollback($input->getOption('simulate'));

        return $deployment->getStatus();
    }
}
