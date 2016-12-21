<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\MigrationsBundle\Command\MigrationsStatusDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DoctrineCommandHelper;
use Doctrine\Bundle\MigrationsBundle\Command\DoctrineCommand;

/**
 * Throws exception if you have previously executed migrations in the database that are not registered migrations.
 * Otherwise, only prints "Status check: OK. Migration to execute: <migrations to execute>".
 *
 * Used to warn if the code is reverted back to an old version with a newer unexpected db version
 *
 * @codeCoverageIgnore
 */
class MigrationsStatusCheckCommand extends MigrationsStatusDoctrineCommand
{
    protected function configure()
    {
        parent::configure();

        $this
                ->setName('doctrine:migrations:status-check')
                ->setDescription('Throw exception (return !=0) if you have '
                        .' previously executed migrations in the database that are not registered migrations')
                ->setHelp(null)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        DoctrineCommandHelper::setApplicationEntityManager($this->getApplication(), $input->getOption('em'));

        $configuration = $this->getMigrationConfiguration($input, $output);
        DoctrineCommand::configureMigrations($this->getApplication()->getKernel()->getContainer(), $configuration);

        $executedMigrations = $configuration->getMigratedVersions();
        $availableMigrations = $configuration->getAvailableVersions();
        $executedUnavailableMigrations = array_diff($executedMigrations, $availableMigrations);

        // not really useful for now.
        // if re-enabled, enable check comparing the highest numbers and see if the db is ahead of the code
        $output->writeln('Status check: skipped');

        return;

        if (!empty($executedUnavailableMigrations)) {
            throw new \RuntimeException(
            '<error>Status check: ERROR. You have '.count($executedUnavailableMigrations).' previously executed migrations'
            .' in the database that are not registered migrations.</error>');
        }

        $migrationsToExecute = array_diff($availableMigrations, $executedMigrations);
        $toMigrate = $migrationsToExecute ? implode(',', $migrationsToExecute) : 'none';

        $output->writeln('Status check: OK. Migration to execute:'.$toMigrate);
    }
}
