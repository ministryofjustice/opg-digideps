<?php

namespace OPG\Digideps\Backend\Command;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;

/**
 * Runs doctrine migrations protected by a PostgreSQL advisory lock.
 *
 * @codeCoverageIgnore
 */
class MigrationsMigrateLockCommand extends Command
{
    /**
     * Arbitrary 64-bit lock key.
     * Change only if you intentionally want a different lock scope.
     */
    private const int LOCK_ID = 0x4449474944455053; // "DIGIDEPS"

    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $verboseLogger,
    ) {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->setName('doctrine:migrations:migrate-lock')
            ->setDescription('Same as doctrine:migrations:migrate, but protected by a PostgreSQL advisory lock.')
            ->addOption(
                'release-lock',
                null,
                InputOption::VALUE_NONE,
                'Release the advisory lock and exit.'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('release-lock')) {
            $this->releaseLock($output);

            return Command::SUCCESS;
        }

        try {
            if (!$this->acquireLock($output)) {
                $message = 'Migration lock is already held by another process. Skipping migration.';
                $this->verboseLogger->warning($message);
                $output->writeln(sprintf('<comment>%s</comment>', $message));

                return Command::SUCCESS;
            }

            $application = $this->getApplication();

            if (!$application instanceof Application) {
                throw new \RuntimeException('Console application is not available');
            }

            $migrationCommand = $application->find('doctrine:migrations:migrate');

            $migrationInput = new ArrayInput([
                '--allow-no-migration' => true,
                '--no-interaction' => true,
            ]);

            $migrationInput->setInteractive(false);

            return $migrationCommand->run($migrationInput, $output);
        } finally {
            try {
                if ($this->connection->isConnected()) {
                    $this->releaseLock($output);
                }
            } catch (\Throwable $e) {
                $this->verboseLogger->error(
                    'Failed to release PostgreSQL advisory lock',
                    ['exception' => $e]
                );
            }
        }
    }

    private function acquireLock(OutputInterface $output): bool
    {
        $acquired = (bool) $this->connection->fetchOne(
            'SELECT pg_try_advisory_lock(?)',
            [self::LOCK_ID]
        );

        $output->writeln(
            $acquired
                ? '<info>Migration advisory lock acquired.</info>'
                : '<comment>Migration advisory lock already held.</comment>'
        );

        return $acquired;
    }

    private function releaseLock(OutputInterface $output): void
    {
        $released = (bool) $this->connection->fetchOne(
            'SELECT pg_advisory_unlock(?)',
            [self::LOCK_ID]
        );

        $output->writeln(
            $released
                ? '<info>Migration advisory lock released.</info>'
                : '<comment>No advisory lock held by this session.</comment>'
        );
    }
}
