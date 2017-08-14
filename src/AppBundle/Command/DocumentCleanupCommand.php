<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Document;
use AppBundle\Exception\RestClientException;
use Psr\Log\LoggerInterface;
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
            ->addOption('ignore-s3-failures', null, InputOption::VALUE_NONE, 'Hard-delete db entry even if the S3 deletion fails');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $restClient = $this->getContainer()->get('rest_client');
        $ignoreS3Failure = $input->getOption('ignore-s3-failures');

        $documents = $restClient->apiCall('GET', '/document/soft-deleted', null, 'Report\Document[]', [], false);
        /* @var $documents Document[] */
        if (count($documents) === 0) {
            $output->write('No documents to delete');
        } else {
            $output->write(count($documents) . ' documents to delete:');
            foreach ($documents as $document) {
                $documentId = $document->getId();
                $storageRef = $document->getStorageReference();
                try {
                    $output->writeln("Document $documentId:");

                    $output->write('  Deleting from S3...');
                    $ret = $this->deleteFromS3($storageRef, $ignoreS3Failure);
                    $message = $ret ? 'OK' : ($ignoreS3Failure ? 'FAIL (ignored)' : 'FAIL');
                    $output->writeln($message);

                    $output->write('  DELETE document/hard-delete/' . $document->getId() . '...');
                    $ret = $restClient->apiCall('DELETE', 'document/hard-delete/' . $document->getId(), null, 'array', [], false);
                    $output->writeln($ret ? 'Deleted' : 'FAIL');

                } catch (\RuntimeException $e) {
                    $message = "Error deleting document $documentId, ref $storageRef. Error: " . $e->getMessage();
                    if ($e instanceof RestClientException) {
                        $message .= print_r($e->getData(), true);
                    }

                    $this->getContainer()->get('logger')->error($message);
                    $output->writeln($message);
                }

            }
            $output->writeln('Done');
        }
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
                $this->logError($e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    /**
     * @return LoggerInterface
     */
    private function logError($message)
    {
        $this->getContainer()->get('logger')->error($message);
    }
}
