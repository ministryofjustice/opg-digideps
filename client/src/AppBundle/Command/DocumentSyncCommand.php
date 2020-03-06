<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Document;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

const SECONDS_IN_A_MINUTE = 1;

class DocumentSyncCommand extends Command
{
    protected static $defaultName = 'digideps:document-sync';

    private $shutdownRequested = false;

    protected function configure()
    {
        $this
            ->setDescription('Uploads queued documents to Sirius and reports back the success')
            ->addOption('daemon', 'd', InputOption::VALUE_NONE, 'Whether to run in daemon mode');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('daemon')) {
            $stopCommand = function() use ($output) {
                $output->writeln('Stopping...');
                $this->shutdownRequested = true;
            };

            pcntl_signal(SIGTERM, $stopCommand);
            pcntl_signal(SIGINT, $stopCommand);
        } else {
            $this->shutdownRequested = true;
        }

        do {
            $this->executeOnce($output);

            pcntl_signal_dispatch();

            if (!$this->shutdownRequested) {
                sleep(SECONDS_IN_A_MINUTE * 10);
            }

        } while (!$this->shutdownRequested);

        return 0;
    }

    private function getQueuedDocuments()
    {
        $doc = new Document();
        $doc->setFileName(mt_rand() . 'example.pdf');
        return [$doc];
    }

    private function executeOnce(OutputInterface $output)
    {
        $documents = $this->getQueuedDocuments();
        $output->writeln($documents[0]->getFileName());
    }
}
