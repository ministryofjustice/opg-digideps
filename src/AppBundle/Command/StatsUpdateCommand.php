<?php

namespace AppBundle\Command;

use AppBundle\Service\StatsService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @codeCoverageIgnore
 */
class StatsUpdateCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    const LOCK_KEY = 'migration_status';
    const LOCK_EXPIRES_SECONDS = 120;

    protected function configure()
    {
        $this
            ->setName('digideps:stats-update')
            ->setDescription('Update stats')
            ->addOption('ignore-lock', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $redis = $this->getContainer()->get('snc_redis.default');

        if (!$input->getOption('ignore-lock')) {
            if ($redis->setnx(self::LOCK_KEY, 1)) {
                $redis->expire(self::LOCK_KEY, self::LOCK_EXPIRES_SECONDS);
            } else {
                $output->writeln('Locked by other job. Expires in ' . self::LOCK_EXPIRES_SECONDS.' seconds');
                return 0;
            }
        }

        try {
        // update stats
        $ret = $this->getContainer()
             ->get('stats_service')
            ->updateAll();

        $output->write("$ret records updated");
        } catch(\Exception $e) {
            $output->writeln($e);
        }

        $redis->del(self::LOCK_KEY); // delete lock
    }
}
