<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Document;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentCleanupCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('digideps:documents-cleanup')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $s3Storage = $this->getContainer()->get('s3_storage');
        $restClient = $this->getContainer()->get('rest_client');

        // TODO open endpoint, with key ? careful about security. extra key maybe ?
        $documents = $restClient->get('/soft-deleted-document'); /* @var $documents Document[] */
        foreach($documents as $document) {
            $s3Storage->delete($document->getStorageReference());
            $restClient->delete('document/'.$document->getId());

        }

    }
}
