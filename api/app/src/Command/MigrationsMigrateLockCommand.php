<?php

namespace OPG\Digideps\Backend\Command;

use Doctrine\DBAL\Connection;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

        $connection = $this->getConnection();

        try {
            if (!$this->acquireLock($output)) {
                $message = 'Migration lock is already held by another process. Skipping migration.';
                $this->getLogger()?->warning($message);
                $output->writeln(sprintf('<comment>%s</comment>', $message));

                return Command::SUCCESS;
            }

            $migrationCommand = $this->getApplication()->find('doctrine:migrations:migrate');

            $migrationInput = new ArrayInput([
                '--allow-no-migration' => true,
                '--no-interaction' => true,
            ]);

            $migrationInput->setInteractive(false);

            return $migrationCommand->run($migrationInput, $output);
        } finally {
            try {
                if ($connection->isConnected()) {
                    $this->releaseLock($output);
                }
            } catch (\Throwable $e) {
                $this->getLogger()?->error(
                    'Failed to release PostgreSQL advisory lock',
                    ['exception' => $e]
                );
            }
        }
    }

    private function acquireLock(OutputInterface $output): bool
    {
        $acquired = (bool) $this->getConnection()->fetchOne(
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
        $released = (bool) $this->getConnection()->fetchOne(
            'SELECT pg_advisory_unlock(?)',
            [self::LOCK_ID]
        );

        $output->writeln(
            $released
                ? '<info>Migration advisory lock released.</info>'
                : '<comment>No advisory lock held by this session.</comment>'
        );
    }

    private function getConnection(): Connection
    {
        return $this->getService('doctrine')->getConnection();
    }

    private function getLogger(): ?object
    {
        return $this->getService('logger');
    }

    private function getService(string $id): mixed
    {
        /** @var Application $application */
        $application = $this->getApplication();

        /** @var ContainerInterface $container */
        $container = $application->getKernel()->getContainer();

        return $container->has($id)
            ? $container->get($id)
            : null;
    }
}
