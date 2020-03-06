<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Document;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentSyncCommand extends DaemonableCommand
{
    protected static $defaultName = 'digideps:document-sync';

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Uploads queued documents to Sirius and reports back the success');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->daemonize($input, $output, function() use ($output) {
            $documents = $this->getQueuedDocuments();
            $output->writeln($documents[0]->getFileName());
        });
    }

    private function getQueuedDocuments()
    {
        $doc = new Document();
        $doc->setFileName(mt_rand() . 'example.pdf');
        return [$doc];
    }
}
