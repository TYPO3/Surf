<?php

namespace TYPO3\Surf\Command;

/*
 * This file is part of TYPO3 Surf.
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

use Humbug\SelfUpdate\Strategy\GithubStrategy;
use Humbug\SelfUpdate\Updater;
use Phar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Surf list command
 */
class SelfUpdateCommand extends Command
{

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return Phar::running() !== '';
    }

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
            )->setDescription(sprintf('Update %s to most recent stable build', $this->getLocalPharName()));
    }

    /**
     * Execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = new Updater(null, false, Updater::STRATEGY_GITHUB);
        /** @var GithubStrategy $strategy */
        $strategy = $updater->getStrategy();
        $strategy->setPackageName('TYPO3/Surf');
        $strategy->setPharName('surf.phar');

        $io = new SymfonyStyle($input, $output);

        $stability = $input->getOption('stability');
        if (empty($stability)) {
            $stability = GithubStrategy::STABLE;
        }
        /** @var GithubStrategy $strategy */
        $strategy = $updater->getStrategy();
        $strategy->setCurrentLocalVersion($this->getApplication()->getVersion());
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
                $output->writeln(sprintf('You have the current %s build installed.', $strategy->getStability()));
            }
        } elseif ($input->getOption('rollback')) {
            $result = $updater->rollback();
            $result ? $output->writeln('Success!') : $output->writeln('Failure!');
        } else {
            $result = $updater->update();

            if ($result) {
                $io->success(
                    sprintf(
                        'Your %s has been updated from "%s" to "%s".',
                        $this->getLocalPharName(),
                        $updater->getOldVersion(),
                        $updater->getNewVersion()
                    )
                );
            } else {
                $io->success(sprintf('Your %s is already up to date.', $this->getLocalPharName()));
            }
        }
    }

    /**
     * @return string
     */
    private function getLocalPharName()
    {
        return basename(Phar::running());
    }
}
