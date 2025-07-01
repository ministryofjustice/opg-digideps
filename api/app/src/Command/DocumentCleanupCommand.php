<?php

declare(strict_types=1);

namespace App\Command;

use App\v2\Service\DocumentService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Remove moribund documents from the database, and tag their associated S3 objects with Purge=1.
 * (The actual S3 objects will be automatically deleted by a policy on a schedule.).
 */
class DocumentCleanupCommand extends Command
{
    public static $defaultName = 'digideps:api:document-cleanup';
    private const JOB_NAME = 'document_cleanup';

    public function __construct(
        private readonly DocumentService $documentService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(
                'Remove any document whose creation date is more than the specified number of '.
                'minutes ago, and which has no associated submission; also mark its associated S3 object for removal.'
            )
            ->addArgument(
                name: 'max-age-minutes',
                mode: InputArgument::OPTIONAL,
                description: 'Set the maximum age in minutes of documents to be retained; any document with a '.
                    'creation data before (now - max-age-minutes) with a null report_submission_id is deleted, '.
                    'and its S3 object marked for removal',
                default: 60 * 48 // two days, in minutes
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // redirect document service logging to this command's output
        $this->documentService->setLogger(new ConsoleLogger($output));

        // the earliest created_on date we'll tolerate
        $minutesOld = intval($input->getArgument('max-age-minutes'));
        $earliestCreatedOn = (new \DateTime())->modify('- '.$minutesOld.' minutes');

        $success = $this->documentService->deleteUnsubmittedDocumentsOlderThan($earliestCreatedOn);

        if (!$success) {
            $output->writeln(
                sprintf(
                    '%s - failure - Document clean up failed. Output: %s',
                    self::JOB_NAME,
                    'what went wrong'
                )
            );

            return Command::FAILURE;
        }

        $output->writeln(
            sprintf(
                '%s - success - Document clean up complete. Output: %s',
                self::JOB_NAME,
                'what went right'
            )
        );

        return Command::SUCCESS;
    }
}
