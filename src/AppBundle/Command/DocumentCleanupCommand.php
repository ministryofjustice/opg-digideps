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
    /**
     * Redis key to use for locking
     */
    const REDIS_LOCK_KEY = 'dd_docs_cleanup';

    /**
     * Expire locks after this number of seconds.
     * this value should be bigger than the time taken to delete the documents at execution time
     */
    const REDIS_LOCK_EXPIRE_SECONDS = 1200;

    protected function configure()
    {
        $this
            ->setName('digideps:documents-cleanup')
            ->addOption('ignore-s3-failures', null, InputOption::VALUE_NONE, 'Hard-delete db entry even if the S3 deletion fails')
            ->addOption('release-lock', null, InputOption::VALUE_NONE, 'Release lock and exit.');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // skip if launched from FRONTEND container
        if ($this->getContainer()->getParameter('env') !== 'admin') {
            $output->writeln('This command can only be executed from admin container');
            exit(1);
        }

        // manual lock releaserelease lock and exit
        if ($input->getOption('release-lock')) {
            $this->releaseLock($output);

            return 0;
        }

        // exit if locked
        if (!$this->acquireLock($output)) {
            return 1;
        }

        $this->cleanUpAllDocuments($input, $output);
        $this->releaseLock($output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function cleanUpAllDocuments(InputInterface $input, OutputInterface $output)
    {
        $ignoreS3Failure = $input->getOption('ignore-s3-failures');

        $documents = $this->getRestClient()->apiCall('GET', '/document/soft-deleted', null, 'Report\Document[]', [], false);
        $toDelete = count($documents);
        $count = 0;
        /* @var $documents Document[] */
        $this->log('info', count($documents) . ' documents to delete:');
        foreach ($documents as $document) {
            $count += $this->cleanUpSingleDocument($document, $ignoreS3Failure) ? 1 : 0;
        }

        $this->log('info', "Done. $toDelete to hard-delete, $count deleted");
    }

    /**
     * @param Document $document
     * @param $ignoreS3Failure
     *
     * @return bool true if deleted from S3 and database
     */
    private function cleanUpSingleDocument(Document $document, $ignoreS3Failure)
    {
        $documentId = $document->getId();
        $storageRef = $document->getStorageReference();

        try {
            $s3Result = $this->deleteFromS3($storageRef, $ignoreS3Failure);
            if ($s3Result) {
                $this->log('info', "deleting $storageRef from S3: success");
            } else {
                $this->log('warning', "deleting $storageRef from S3: " . (($ignoreS3Failure ? 'FAIL (ignored)' : 'FAIL')));
            }

            $endpointResult = $this->getRestClient()->apiCall('DELETE', 'document/hard-delete/' . $document->getId(), null, 'array', [], false);
            if ($endpointResult) {
                $this->log('info', "Document $documentId deleted successfully from db");
            } else {
                $this->log('error', "Document $documentId delete API failure");
            }

            return $s3Result && $endpointResult;

        } catch (\RuntimeException $e) {
            $message = "can't delete $documentId, ref $storageRef. Error: " . $e->getMessage();
            if ($e instanceof RestClientException) {
                $message .= print_r($e->getData(), true);
            }

            $this->log('error', $message);
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

            return true;
        } catch (\Exception $e) {
            if ($ignoreS3Failure) {
                $this->log('error', $e->getMessage());
            } else {
                throw $e;
            }
        }
    }


    /**
     * @return bool true if lock if acquired, false if not (already acquired)
     */
    private function acquireLock()
    {
        $ret = $this->getRedis()->setnx(self::REDIS_LOCK_KEY, true) == 1;
        $this->getRedis()->expire(self::REDIS_LOCK_KEY, self::REDIS_LOCK_EXPIRE_SECONDS);
        $this->log('info', $ret ? 'Lock acquired' : 'Cannot acquire lock, already acquired');

        return $ret;
    }


    /**
     * release lock.
     *
     * @param type $output
     */
    private function releaseLock()
    {
        $this->log('info', 'Lock released');

        return $this->getRedis()->del(self::REDIS_LOCK_KEY);
    }

    /**
     * @return \Predis\Client
     */
    private function getRedis()
    {
        return $this->getContainer()->get('snc_redis.default');
    }

    /**
     * @return \AppBundle\Service\Client\RestClient|object
     */
    private function getRestClient()
    {
        return $this->getContainer()->get('rest_client');
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
