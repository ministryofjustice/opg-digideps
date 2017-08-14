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
            ->addOption('ignore-s3-failures', null, InputOption::VALUE_NONE, 'Hard-delete db entry even if the S3 deletion fails')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $restClient = $this->getContainer()->get('rest_client');
        $ignoreS3Failure = $input->getOption('ignore-s3-failures');

        // TODO open endpoint, with key ? careful about security. extra key maybe ?
        $documents = $restClient->apiCall('GET', '/document/soft-deleted', null, 'Report\Document[]', [], false); /* @var $documents Document[] */
        $output->write(count($documents).' documents to delete:');
        foreach($documents as $document) {
            $documentId = $document->getId();
            $storageRef = $document->getStorageReference();
            try {
                $this->deleteFromS3($document->getStorageReference(), $ignoreS3Failure);
                $restClient->apiCall('DELETE', 'document/hard-delete/'.$document->getId(), null, 'raw', [], false);
                $output->write('.');
            } catch (\RuntimeException $e) {
                $message = "Error deleting document $documentId, ref $storageRef. Error: ".$e->getMessage();
                if ($e instanceof RestClientException) {
                    $message .= print_r($e->getData(), true);
                }

                $this->getContainer()->get('logger')->error($message);
                $output->writeln($message);
            }

        }
        $output->writeln('Done');
    }

    /**
     * @param string $ref
     * @param boolean $ignoreS3Failure
     *
     * @throws \Exception
     */
    private function deleteFromS3($ref, $ignoreS3Failure)
    {
        $s3Storage = $this->getContainer()->get('s3_storage');

        try {
            $s3Storage->delete($ref);
        } catch (\Exception $e) {
            if ($ignoreS3Failure) {
                $this->getContainer()->get('logger')->error($e->getMessage());
            } else {
                throw $e;
            }
        }
    }
}
