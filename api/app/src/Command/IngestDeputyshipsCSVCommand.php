<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\File\Storage\S3Storage;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVIngester;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class IngestDeputyshipsCSVCommand extends Command
{
    public static $defaultName = 'digideps:api:ingest-deputyships-csv';
    private const JOB_NAME = 'deputyships_csv_processing';

    public function __construct(
        private readonly S3Client $s3,
        private readonly ParameterBagInterface $params,
        private readonly DeputyshipsCSVIngester $deputyshipsCSVIngester,
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
        $deputyshipsCSVFile = $input->getArgument('csv-filename');
        $fileLocation = sprintf('/tmp/%s', $deputyshipsCSVFile);

        try {
            $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $deputyshipsCSVFile,
                'SaveAs' => $fileLocation,
            ]);
        } catch (S3Exception $e) {
            $logMessage = 'Error retrieving file %s from bucket %s';
            if (in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES)) {
                $logMessage .= ' - file %s not found in bucket %s';
            }
            $logMessage = sprintf($logMessage, $deputyshipsCSVFile, $bucket);

            $this->verboseLogger->error($logMessage);
            $output->writeln(sprintf('%s - failure - %s', self::JOB_NAME, $logMessage));

            return Command::FAILURE;
        }

        $result = $this->deputyshipsCSVIngester->processCsv($fileLocation);

        if (!$result->success) {
            $output->writeln(
                sprintf(
                    '%s - failure - Output: %s',
                    self::JOB_NAME,
                    $result->message
                )
            );

            return Command::FAILURE;
        }

        if (!unlink($fileLocation)) {
            $logMessage = sprintf('Unable to delete file %s', $fileLocation);

            $this->verboseLogger->error($logMessage);
            $output->writeln(
                sprintf(
                    '%s failure - (partial) - %s Output: %s',
                    self::JOB_NAME,
                    $logMessage,
                    'successfully processed deputyships CSV, but could not remove file'
                )
            );

            return Command::SUCCESS;
        }

        $output->writeln(
            sprintf(
                '%s - success - Finished processing deputyships CSV, Output: %s',
                self::JOB_NAME,
                $result->message
            )
        );

        return Command::SUCCESS;
    }
}
