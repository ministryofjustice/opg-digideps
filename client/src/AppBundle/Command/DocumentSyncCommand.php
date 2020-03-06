<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentSyncCommand extends Command
{
    protected static $defaultName = 'digideps:document-sync';

    protected function configure()
    {
        $this
            ->setDescription('Uploads queued documents to Sirius and reports back the success')
            ->addOption('daemon', 'd', InputOption::VALUE_NONE, 'Whether to run in daemon mode');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Test');
    }
}
