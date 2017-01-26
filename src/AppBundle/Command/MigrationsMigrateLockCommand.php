<?php

namespace AppBundle\Command;

use Doctrine\Bundle\MigrationsBundle\Command\MigrationsMigrateDoctrineCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * command that launches doctrine migration,
 * using redis to implement locking in order to prevent concurrent execution.
 *
 * @codeCoverageIgnore
 */
class MigrationsMigrateLockCommand extends MigrationsMigrateDoctrineCommand
{
    const LOCK_KEY = 'migration_status';
    const LOCK_VALUE = 'locked';

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('doctrine:migrations:migrate-lock')
            ->setDescription('Same as doctrine:migrations:migrate, but locking the database.')
            ->setHelp(null)
            ->addOption('release-lock', null, InputOption::VALUE_NONE, 'Release lock and exit.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // release lock and exit
        if ($input->getOption('release-lock')) {
            $this->releaseLock($output);

            return 0;
        }

        try {
            if ($this->acquireLock($output)) {
                $returnCode = parent::execute($input, $output);
                $this->releaseLock($output);

                return $returnCode;
            } else {
                $message = 'Migration is locked by another migration, skipped. Launch with --release-lock if needed.';
                $this->getService('logger')->warning($message);
                $output->writeln($message);

                return 0;
            }
        } catch (\Exception $e) {
            // in case of exception, delete the lock, then re-throw to keep the parent behaviour
            $this->releaseLock($output);

            throw $e;
        }
    }

    /**
     * @return bool true if lock if acquired, false if not (already acquired)
     */
    private function acquireLock($output)
    {
        $ret = $this->getRedis()->setnx(self::LOCK_KEY, self::LOCK_VALUE) == 1;
        $output->writeln($ret ? 'Lock acquired.' : 'Cannot acquire lock, already acquired.');

        return $ret;
    }

    /**
     * release lock.
     *
     * @param type $output
     */
    private function releaseLock($output)
    {
        $output->writeln('Lock released.');

        return $this->getRedis()->del(self::LOCK_KEY);
    }

    /**
     * @return \Predis\Client
     */
    private function getRedis()
    {
        return $this->getService('snc_redis.default');
    }

    private function getService($id)
    {
        return $this->getApplication()->getKernel()->getContainer()->get($id);
    }
}
