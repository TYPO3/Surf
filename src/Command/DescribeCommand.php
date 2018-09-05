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
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\FailedDeployment;
use TYPO3\Surf\Domain\Model\SimpleWorkflow;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Integration\FactoryAwareInterface;
use TYPO3\Surf\Integration\FactoryAwareTrait;

/**
 * Surf describe command
 */
class DescribeCommand extends Command implements FactoryAwareInterface
{
    use FactoryAwareTrait;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('describe')
             ->setDescription('Describes the flow for the given name')
             ->addArgument(
                 'deploymentName',
                 InputArgument::REQUIRED,
                 'The deployment name'
             )
             ->addOption(
                 'configurationPath',
                 null,
                 InputOption::VALUE_OPTIONAL,
                 'Path for deployment configuration files'
             );
    }

    /**
     * Prints configuration for the
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $configurationPath = $input->getOption('configurationPath');
        $deploymentName = $input->getArgument('deploymentName');
        $deployment = $this->factory->getDeployment($deploymentName, $configurationPath);
        $workflow = $deployment->getWorkflow();

        if (! $deployment instanceof FailedDeployment) {
            $output->writeln('<success>Deployment ' . $deployment->getName() . '</success>');
            $output->writeln('');
            $output->writeln('Workflow: <success>' . $workflow->getName() . '</success>');

            if ($workflow instanceof SimpleWorkflow) {
                $value = $workflow->isEnableRollback() ? 'true' : 'false';
                $output->writeln('    <comment>Rollback enabled:</comment> <info>' . $value . '</info>');
            }

            $output->writeln('');

            $this->printNodes($deployment->getNodes());

            $this->printApplications($deployment->getApplications(), $deployment->getWorkflow());
        }
    }

    /**
     * @param array $nodes
     */
    protected function printNodes($nodes)
    {
        $this->output->writeln('Nodes:' . PHP_EOL);
        foreach ($nodes as $node) {
            $this->output->writeln('  <success>' . $node->getName() . '</success> <info>(' . $node->getHostname() . ')</info>');
        }
    }

    /**
     * Prints configuration for each defined application
     *
     * @param array $applications
     * @param Workflow $workflow
     */
    protected function printApplications(array $applications, Workflow $workflow)
    {
        $this->output->writeln(PHP_EOL . 'Applications:' . PHP_EOL);
        foreach ($applications as $application) {
            $this->output->writeln('  <success>' . $application->getName() . ':</success>');
            $this->output->writeln('    <comment>Deployment path</comment>: <success>' . $application->getDeploymentPath() . '</success>');
            $this->output->writeln('    <comment>Options</comment>: ');
            foreach ($application->getOptions() as $key => $value) {
                if (is_array($value)) {
                    $this->output->writeln('      ' . $key . ' =>');
                    foreach ($value as $item) {
                        $this->output->writeln(sprintf('        <success>%s</success>', $item));
                    }
                } else {
                    $printableValue = is_scalar($value) ? $value : gettype($value);
                    $this->output->writeln('      ' . $key . ' => <success>' . $printableValue . '</success>');
                }
            }
            $this->output->writeln('    <comment>Nodes</comment>: <success>' . implode(
                ', ',
                    $application->getNodes()
            ) . '</success>');

            if ($workflow instanceof SimpleWorkflow) {
                $this->output->writeln('    <comment>Detailed workflow</comment>: ');
                $this->printStages($application, $workflow->getStages(), $workflow->getTasks());
            }
        }
    }

    /**
     * Prints stages and contained tasks for given application
     *
     * @param Application $application
     * @param array $stages
     * @param array $tasks
     */
    protected function printStages(Application $application, array $stages, array $tasks)
    {
        foreach ($stages as $stage) {
            $this->output->writeln('      <comment>' . $stage . ':</comment>');
            foreach (['before', 'tasks', 'after'] as $stageStep) {
                $output = '';
                foreach (['_', $application->getName()] as $applicationName) {
                    $label = $applicationName === '_' ? 'for all applications' : 'for application ' . $applicationName;
                    if (isset($tasks['stage'][$applicationName][$stage][$stageStep])) {
                        foreach ($tasks['stage'][$applicationName][$stage][$stageStep] as $task) {
                            $this->printBeforeAfterTasks($tasks, $applicationName, $task, 'before', $output);
                            $output .= '          <success>' . $task . '</success> <info>(' . $label . ')</info>' . PHP_EOL;
                            $this->printBeforeAfterTasks($tasks, $applicationName, $task, 'after', $output);
                        }
                    }
                }

                if (strlen($output) > 0) {
                    $this->output->writeln('        <info>' . $stageStep . ':</info>');
                }
                $this->output->write($output);
            }
        }
    }

    /**
     * Print all tasks before or after a task
     *
     * @param array $tasks
     * @param string $applicationName
     * @param string $task
     * @param string $step
     * @param string $output
     */
    private function printBeforeAfterTasks(array $tasks, $applicationName, $task, $step, &$output)
    {
        $label = $applicationName === '_' ? 'for all applications' : 'for application ' . $applicationName;
        if (isset($tasks[$step][$applicationName][$task])) {
            // 'Task "' . $beforeTask . '" before "' . $task
            foreach ($tasks[$step][$applicationName][$task] as $beforeAfterTask) {
                $output .= '          <success>Task ' . $beforeAfterTask . ' ' . $step . ' ' . $task . '</success> <info>(' . $label . ')</info>' . PHP_EOL;
            }
        }
    }
}
