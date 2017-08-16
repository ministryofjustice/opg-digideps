<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Document;
use AppBundle\Exception\RestClientException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

/**
 * Class DocumentCleanupCommand
 * @package AppBundle\Command
 */
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
        // skip if launched from FRONTEND container
        if ($this->getContainer()->getParameter('env') !== 'admin') {
            $output->writeln('This command can only be executed from admin container');
            exit(1);
        }

        $restClient = $this->getContainer()->get('rest_client');
        $ignoreS3Failure = $input->getOption('ignore-s3-failures');

        $documents = $restClient->apiCall('GET', '/document/soft-deleted', null, 'Report\Document[]', [], false);
        $toDelete = count($documents);
        $count = 0;
        /* @var $documents Document[] */
        $this->log('info', count($documents) . ' documents to delete:');
        foreach ($documents as $document) {
            $documentId = $document->getId();
            $storageRef = $document->getStorageReference();
            try {
                $s3Result = $this->deleteFromS3($storageRef, $ignoreS3Failure);
                if ($s3Result) {
                    $this->log('info', "deleting $storageRef from S3: success");
                } else {
                    $this->log('warning', "deleting $storageRef from S3: " . (($ignoreS3Failure ? 'FAIL (ignored)' : 'FAIL')));
                }

                $endpointResult = $restClient->apiCall('DELETE', 'document/hard-delete/' . $document->getId(), null, 'array', [], false);
                if ($endpointResult) {
                    $this->log('info', "Document $documentId deleted successfully from db");
                } else {
                    $this->log('error', "Document $documentId delete API failure");
                }

                $count += ($s3Result && $endpointResult) ? 1 : 0;

            } catch (\RuntimeException $e) {
                $message = "can't delete $documentId, ref $storageRef. Error: " . $e->getMessage();
                if ($e instanceof RestClientException) {
                    $message .= print_r($e->getData(), true);
                }

                $this->log('error', $message);
            }

        }

        $this->log('info', "Done. $toDelete to hard-delete, $count deleted");
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
                $this->log('error', $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param $level
     * @param $message
     * @return object
     */
    private function log($level, $message)
    {
        $this->getContainer()->get('logger')->log($level, $message, ['extra' => [
            'cron' => 'digideps:documents-cleanup',
        ]]);


        return $this->getContainer()->get('logger');
    }
}
