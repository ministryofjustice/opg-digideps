<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\File\Storage\S3Storage;
use App\v2\Registration\DeputyshipProcessing\CourtOrdersCSVProcessor;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProcessCourtOrdersCSVCommand extends Command
{
    public static $defaultName = 'digideps:api:process-court-orders-csv';
    private const JOB_NAME = 'courtorder_csv_processing';

    public function __construct(
        private readonly S3Client $s3,
        private readonly ParameterBagInterface $params,
        private readonly CourtOrdersCSVProcessor $courtOrdersCSVProcessor,
        private readonly LoggerInterface $verboseLogger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Processes the CourtOrder CSV Report from the S3 bucket.')
            ->addArgument('csv-filename', InputArgument::REQUIRED, 'Specify the file name of the CSV to retreive');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bucket = $this->params->get('s3_sirius_bucket');
        $courtOrdersFile = $input->getArgument('csv-filename');
        $fileLocation = sprintf('/tmp/%s', $courtOrdersFile);

        try {
            $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $courtOrdersFile,
                'SaveAs' => $fileLocation,
            ]);
        } catch (S3Exception $e) {
            if (in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES)) {
                $logMessage = 'File %s not found in bucket %s';
            } else {
                $logMessage = 'Error retrieving file %s from bucket %s';
            }
            $logMessage = sprintf($logMessage, $courtOrdersFile, $bucket);

            $this->verboseLogger->error($logMessage);
            $output->writeln(sprintf('%s - failure - %s', self::JOB_NAME, $logMessage));

            return Command::FAILURE;
        }

        $result = $this->courtOrdersCSVProcessor->processFile($fileLocation);

        if (!$result->success) {
            $output->writeln(
                sprintf(
                    '%s - failure - Output: %s',
                    self::JOB_NAME,
                    '...error from the processor...'
                )
            );

            return Command::FAILURE;
        }

        if (!unlink($fileLocation)) {
            $logMessage = sprintf('Unable to delete file %s', $fileLocation);

            $this->verboseLogger->error($logMessage);
            $output->writeln(
                sprintf(
                    '%s failure - (partial) - %s processing Output: %s',
                    self::JOB_NAME,
                    $logMessage,
                    'successfully processed court order CSV, but could not remove file'
                )
            );

            return Command::SUCCESS;
        }

        $output->writeln(
            sprintf(
                '%s - success - Finished processing CourtOrderCSV, Output: %s',
                self::JOB_NAME,
                '...success output from the processor...'
            )
        );

        return Command::SUCCESS;
    }
}
