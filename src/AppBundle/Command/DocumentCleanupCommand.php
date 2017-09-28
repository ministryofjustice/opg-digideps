<?php

namespace AppBundle\Command;

use AppBundle\Entity\Report\Document;
use AppBundle\Exception\RestClientException;
use AppBundle\Service\DocumentService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
     * Expire locks after this number of second, so that a abnormal script termination won't lock it forever
     */
    const REDIS_LOCK_EXPIRE_SECONDS = 600;

    protected function configure()
    {
        $this
            ->setName('digideps:documents-cleanup')
            ->addOption('ignore-s3-failures', null, InputOption::VALUE_NONE, 'Hard-delete db entry even if the S3 deletion fails')
            ->addOption('release-lock', null, InputOption::VALUE_NONE, 'Release lock and exit.')
            ->addOption('skip-admin-check', null, InputOption::VALUE_NONE, 'skip the check requiring env==admin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // skip if launched from FRONTEND container
        if (!$input->getOption('skip-admin-check') && $this->getContainer()->getParameter('env') !== 'admin') {
            $output->writeln('This command can only be executed from admin container');
            return 1;
        }

        // manual lock release and exit
        if ($input->getOption('release-lock')) {
            $this->releaseLock();
            $output->writeln('Lock released. You can now relaunch.');
            return 0;
        }

        // exit if locked
        // $this->getContainer()->getParameter('kernel.debug')
        if (!$this->acquireLock($output)) {
            $output->writeln('Locked. try later or launch with unlock flag.');
            return 1;
        }

        $ignoreS3Failures = $input->getOption('ignore-s3-failures');
        $documentService = $this->getContainer()->get('document_service');

        /* @var $documentService DocumentService */
        $documentService->removeSoftDeleted($ignoreS3Failures);
        $documentService->removeOldReportSubmissions($ignoreS3Failures);

        $this->releaseLock($output);
    }

    private function updateLockTtl()
    {
        $this->getRedis()->expire(self::REDIS_LOCK_KEY, self::REDIS_LOCK_EXPIRE_SECONDS);
    }

    /**
     * @return bool true if lock if acquired, false if not (already acquired)
     */
    private function acquireLock()
    {
        $ret = $this->getRedis()->setnx(self::REDIS_LOCK_KEY, true) == 1;
        if ($ret) {
            $this->updateLockTtl();
        } else {
            $currentTtl = $this->getRedis()->ttl(self::REDIS_LOCK_KEY);
            $this->log('warning', "Cannot acquire lock, already acquired. Expiring in $currentTtl seconds");
        }

        return $ret;
    }

    /**
     * Delete redis key used for locking
     */
    private function releaseLock()
    {
        $this->getRedis()->del(self::REDIS_LOCK_KEY);
    }

    /**
     * @return \Predis\Client
     */
    private function getRedis()
    {
        return $this->getContainer()->get('snc_redis.default');
    }


    /**
     * Log message using the internal logger
     *
     * @param $level
     * @param $message
     */
    private function log($level, $message)
    {
        //echo $message."\n"; //enable for debugging reasons. Tail the log with log-level=info otherwise

        $this->getContainer()->get('logger')->log($level, $message, ['extra' => [
            'cron' => 'digideps:documents-cleanup',
        ]]);
    }

}
