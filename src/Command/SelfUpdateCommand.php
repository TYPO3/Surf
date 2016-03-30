<?php
namespace TYPO3\Surf\Command;

/*                                                                        *
 * This script belongs to the TYPO3 project "TYPO3 Surf".                 *
 *                                                                        *
 *                                                                        */

use Humbug\SelfUpdate\Strategy\GithubStrategy;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\Surf\Integration\FactoryAwareInterface;
use TYPO3\Surf\Integration\FactoryAwareTrait;

/**
 * Surf list command
 */
class SelfUpdateCommand extends Command implements FactoryAwareInterface
{
    use FactoryAwareTrait;

    /**
     * Configure
     */
    protected function configure()
    {
        $this->setName('self-update')
            ->addOption(
                'stability',
                null,
                InputOption::VALUE_OPTIONAL,
                'GitHub stability value (' . GithubStrategy::STABLE . ', ' . GithubStrategy::UNSTABLE . ', ' . GithubStrategy::ANY . ')'
            )->addOption(
                'check',
                null,
                InputOption::VALUE_NONE,
                'Check for new version'
            )->addOption(
                'rollback',
                null,
                InputOption::VALUE_NONE,
                'Rolls back to previous version'
            );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return \Phar::running() !== '';
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = new Updater(null, false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $strategy = $updater->getStrategy();
        /* @var GithubStrategy $strategy */
        $strategy->setPackageName('TYPO3/Surf');
        $strategy->setPharName('surf.phar');
        $strategy->setCurrentLocalVersion($this->getApplication()->getVersion());

        $stability = $input->getOption('stability');
        if (empty($stability)) {
            // Unstable by default. Should be removed once we have a 2.0.0 final
            $stability = GithubStrategy::UNSTABLE;
        }
        $strategy->setStability($stability);

        if ($input->getOption('check')) {
            $result = $updater->hasUpdate();
            if ($result) {
                $output->writeln(sprintf(
                    'The %s build available remotely is: %s',
                    $strategy->getStability() === GithubStrategy::ANY ? 'latest' : 'current ' . $strategy->getStability(),
                    $updater->getNewVersion()
                ));
            } elseif (false === $updater->getNewVersion()) {
                $output->writeln('There are no new builds available.');
            } else {
                $output->writeln('You have the current stable build installed.');
            }
        } elseif ($input->getOption('rollback')) {
            $result = $updater->rollback();
            $result ? $output->writeln('Success!') : $output->writeln('Failure!');
        } else {
            $result = $updater->update();
            $result ? $output->writeln('Updated.') : $output->writeln('No update needed!');
        }
    }
}
