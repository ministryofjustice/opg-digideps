<?php

namespace AppBundle\Command;

use AppBundle\Entity\CasRec;
use AppBundle\Service\CarecService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command is installed in the crontab and executed every X minutes
 * It could be called by more than one API container, or re-called by the corntab when is already running
 * A redis-lock mechanism is implemented to only allow one execution a time
 * The lock expires after a certain amount of time, to allow an execution in case the script is  killed
 * before it releases the lock
 *
 */
class StatsUpdateCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    const LOCK_KEY = 'migration_status';

    const LOCK_EXPIRES_SECONDS = 1800; // in order not avoid multiple API containers running this.in case of crashes, in 30 minutes the lock will be released

    protected function configure()
    {
        $this
            ->setName('digideps:stats-update')
            ->setDescription('Update stats')
            ->addOption('release-lock', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $redis = $this->getContainer()->get('snc_redis.default');

        // redis lock logic
        if (!$input->getOption('release-lock')) {
            $redis->del(self::LOCK_KEY);
        }
        if ($redis->setnx(self::LOCK_KEY, 1)) {
            $redis->expire(self::LOCK_KEY, self::LOCK_EXPIRES_SECONDS);
        } else {
            $output->writeln('Locked by other job. Expires in ' . self::LOCK_EXPIRES_SECONDS . ' seconds');

            return 0;
        }

        try {
            // update stats
            $statsService = $this->getContainer()->get('casrec_service');
            $nUpdated = $statsService->updateAll();
            $output->writeln($nUpdated ? "Updated $nUpdated CASREC records" : "No more CASREC records to updated");
            $linesCount = $statsService->saveCsv(CasRec::STATS_FILE_PATH);
            $output->writeln("Stats file written. {$linesCount} lines written");
        } catch (\Exception $e) {
            $output->writeln($e);
        }

        $redis->del(self::LOCK_KEY); // delete lock
        $output->writeln("Job end. lock released");
    }
}
