<?php

namespace AppBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DoctrineCommandHelper;
use Doctrine\Bundle\MigrationsBundle\Command\DoctrineCommand;
use Doctrine\Bundle\MigrationsBundle\Command\MigrationsStatusDoctrineCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
    protected function configure(): void
    {
        parent::configure();

        $this
                ->setName('doctrine:migrations:status-check')
                ->setDescription('Throw exception (return !=0) if you have '
                        . ' previously executed migrations in the database that are not registered migrations')
                ->setHelp(null)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $infos = $this->dependencyFactory->getMigrationStatusInfosHelper()->getMigrationsInfos();
        $key = 'Executed Unavailable Migrations';

        if (!isset($infos[$key])) {
            throw new \RuntimeException('Cannot safely identify unavailable migrations');
        }

        /**
         * @var int
         */
        $executedUnavailableMigrations = $infos[$key];

        if ($executedUnavailableMigrations > 0) {
            throw new \RuntimeException(
            '<error>Status check: ERROR. You have ' . $executedUnavailableMigrations . ' previously executed migrations'
            . ' in the database that are not registered migrations.</error>');
        }

        $output->writeln('Status check: OK.');
        return null;
    }
}
