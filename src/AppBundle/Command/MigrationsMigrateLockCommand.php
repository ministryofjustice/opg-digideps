<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\MigrationsBundle\Command\MigrationsMigrateDoctrineCommand;

/**
 * command that launches doctrine migration, 
 * using redis to implement locking in order to prevent concurrent execution
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
            ->addOption('clear-lock', null, InputOption::VALUE_NONE, 'Only delete migration lock and exit.')
            ->addOption('write-lock', null, InputOption::VALUE_NONE, 'Manually write lock, for testing purposes.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // debug command to clear lock
        if ($input->getOption('clear-lock')) {
            $this->deleteLock();
            $output->writeln('Lock cleared.');
            return 0;
        }
        
        // debug command to write lock
        if ($input->getOption('write-lock')) {
            $this->writeLock();
            $output->writeln('Lock written.');
            return 0;
        }

        // skip migration if locked
        if ($this->isLocked()) {
            $message = 'Migration is locked by another migration, skipped.'
                       . ' Launch with -- clear-lock to manually delete the log';
            $this->getService('logger')->warning($message);
            $output->writeln($message);
            return 0;
        }

        
        $this->writeLock();
        $output->writeln('Lock written.');
        try {
            $ret = parent::execute($input, $output);
            $this->deleteLock();
            $output->writeln('Lock deleted.');
        } catch (\Exception $e) {
            // in case of exception, delete the lock, then re-throw to keep the parent behaviour
            $this->deleteLock();
            $output->writeln('Exception raised. Lock deleted.');
            throw $e;
        }
        
        return $ret;
    }

    /**
     * @return boolean
     */
    private function isLocked()
    {
        return $this->getRedis()->get(self::LOCK_KEY) === self::LOCK_VALUE;
    }

    private function writeLock()
    {
        $this->getRedis()->set(self::LOCK_KEY, self::LOCK_VALUE);
        if ($this->getRedis()->get(self::LOCK_KEY) !== self::LOCK_VALUE) {
            throw new \RuntimeException('Cannot write the lock value into redis');
        }
    }

    private function deleteLock()
    {
        $this->getRedis()->set(self::LOCK_KEY, null);
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