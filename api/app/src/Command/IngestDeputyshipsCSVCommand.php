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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
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
            ->setDescription('Processes the Deputyships CSV Report from the S3 bucket.')
            ->addArgument('csv-filename', InputArgument::REQUIRED, 'Specify the file name of the CSV to retreive')
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_OPTIONAL,
                'CSV ingest and candidates are always applied, and the builder always creates court order entities
                and relationships; but if this is set to true, the court order changes are rolled back and not persisted
                to the database',
                'false'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $bucket */
        $bucket = $this->params->get('s3_sirius_bucket');

        /** @var string $deputyshipsCSVFile */
        $deputyshipsCSVFile = $input->getArgument('csv-filename');

        $dryRun = ('true' === $input->getOption('dry-run'));
        if ($dryRun) {
            $output->writeln('*** deputyships-ingest running in DRY RUN mode: court order data will not be saved ***');
        }

        $fileLocation = "/tmp/$deputyshipsCSVFile";

        try {
            $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $deputyshipsCSVFile,
                'SaveAs' => $fileLocation,
            ]);
        } catch (S3Exception $e) {
            $logMessage = "Error retrieving file $deputyshipsCSVFile from bucket $bucket";
            if (in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES)) {
                $logMessage .= ' - file not found';
            }

            $this->verboseLogger->error($logMessage);
            $output->writeln(sprintf('%s - failure - %s', self::JOB_NAME, $logMessage));

            return Command::FAILURE;
        }

        try {
            $logger = new ConsoleLogger($output);
            $this->deputyshipsCSVIngester->setLogger($logger);

            $result = $this->deputyshipsCSVIngester->processCsv($fileLocation, $dryRun);
        } catch (\Exception $e) {
            $output->writeln(
                sprintf(
                    '%s - failure - Unexpected exception occurred while processing CSV: %s',
                    self::JOB_NAME,
                    $e->getMessage(),
                )
            );

            $this->verboseLogger->error($e->getTraceAsString());

            return Command::FAILURE;
        }

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
