<?php

namespace App\Command;

use Predis\Client;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * command that launches doctrine migration,
 * using redis to implement locking in order to prevent concurrent execution.
 *
 * @codeCoverageIgnore
 */
class MigrationsMigrateLockCommand extends Command
{
    const LOCK_KEY = 'migration_status';
    const LOCK_VALUE = 'locked';
    const LOCK_EXPIRES_SECONDS = 300;

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('doctrine:migrations:migrate-lock')
            ->setDescription('Same as doctrine:migrations:migrate, but locking the database.')
            ->setHelp('')
            ->addOption('release-lock', null, InputOption::VALUE_NONE, 'Release lock and exit.')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): ?int
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
        } catch (\Throwable $e) {
            // in case of exception, delete the lock, then re-throw to keep the parent behaviour
            $this->releaseLock($output);

            throw $e;
        }
    }

    /**
     * @param OutputInterface $output
     * @return bool true if lock if acquired, false if not (already acquired)
     */
    private function acquireLock(OutputInterface $output): bool
    {
        $ret = $this->getRedis()->setnx(self::LOCK_KEY, self::LOCK_VALUE) == 1;
        $this->getRedis()->expire(self::LOCK_KEY, self::LOCK_EXPIRES_SECONDS);
        $output->writeln($ret ? 'Lock acquired.' : 'Cannot acquire lock, already acquired.');

        return $ret;
    }

    /**
     * release lock.
     *
     * @param OutputInterface $output
     * @return int
     */
    private function releaseLock(OutputInterface $output): int
    {
        $output->writeln('Lock released.');

        return $this->getRedis()->del(self::LOCK_KEY);
    }

    /**
     * @return Client
     */
    private function getRedis(): Client
    {
        return $this->getService('snc_redis.default');
    }

    /**
     * @param string $id
     * @return mixed
     */
    private function getService(string $id): mixed
    {
        /** @var Application $application */
        $application = $this->getApplication();

        /** @var ContainerInterface $container */
        $container = $application->getKernel()->getContainer();

        return $container->get($id);
    }
}
