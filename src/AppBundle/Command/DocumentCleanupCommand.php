<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Document;
use AppBundle\Exception\RestClientException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

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
        $documents = $restClient->apiCall('GET', '/document/soft-deleted', null, 'Report\Document[]', [], false); /* @var $documents Document[] */
        foreach($documents as $document) {
            $documentId = $document->getId();
            $storageRef = $document->getStorageReference();
            try {
                if (!$storageRef) {
                    throw new RuntimeException('Storage reference is empty');
                }
                $s3Storage->delete($document->getStorageReference());

                // database delete. Won't be done if the S3 delete fails
                $restClient->delete('document/hard-delete/'.$document->getId());

            } catch (\RuntimeException $e) {
                $message = "Error deleting document $documentId, ref $storageRef. Error: ".$e->getMessage();
                if ($e instanceof RestClientException) {
                    $message .= print_r($e->getData(), true);
                }

                $this->getContainer()->get('logger')->error($message);
                $output->writeln($message);
            }

        }

    }
}
