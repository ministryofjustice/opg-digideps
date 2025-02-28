<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DataImporter\CsvToArray;
use App\Service\File\Storage\S3Storage;
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

    private const CHUNK_SIZE = 50;

    private const EXPECTED_COLUMNS = [
    ];

    private array $processingOutput = [
        'added' => 0,
        'skipped' => 0,
        'updated' => 0,
        'errors' => [],
    ];

    private OutputInterface $cliOutput;

    public function __construct(
        private readonly S3Client $s3,
        private readonly ParameterBagInterface $params,
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
        $this->cliOutput = $output;
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
            $this->cliOutput->writeln(sprintf('%s - failure - %s', self::JOB_NAME, $logMessage));

            return Command::FAILURE;
        }

        $data = $this->csvToArray($fileLocation);
        if (count($data) >= 1 && $this->process($data)) {
            if (!unlink($fileLocation)) {
                $logMessage = sprintf('Unable to delete file %s', $fileLocation);

                $this->verboseLogger->error($logMessage);
                $this->cliOutput->writeln(
                    sprintf(
                        '%s failure - (partial) - %s processing Output: %s',
                        self::JOB_NAME,
                        $logMessage,
                        $this->processedStringOutput()
                    )
                );

                return Command::SUCCESS;
            }

            $this->cliOutput->writeln(
                sprintf(
                    '%s - success - Finished processing CourtOrderCSV, Output: %s',
                    self::JOB_NAME,
                    $this->processedStringOutput()
                )
            );

            return Command::SUCCESS;
        }

        $this->cliOutput->writeln(
            sprintf(
                '%s - failure - Output: %s',
                self::JOB_NAME,
                'Process failed for unknown reason'
            )
        );

        return Command::FAILURE;
    }

    private function csvToArray(string $fileName): array
    {
        try {
            return (new CsvToArray(self::EXPECTED_COLUMNS))->create($fileName);
        } catch (\RuntimeException $e) {
            $logMessage = sprintf('Error processing CSV: %s', $e->getMessage());

            $this->verboseLogger->error($logMessage);
            $this->cliOutput->writeln(self::JOB_NAME.' - failure - '.$logMessage);
        }

        return [];
    }

    private function process(mixed $data): bool
    {
        if (is_array($data)) {
            $chunks = array_chunk($data, self::CHUNK_SIZE);

            foreach ($chunks as $index => $chunk) {
                $this->verboseLogger->notice(sprintf('Uploading chunk with Id: %s', $index));
            }

            return true;
        }

        return false;
    }

    private function storeOutput(array $processingOutput): void
    {
        if (!empty($processingOutput['errors'])) {
            $this->processingOutput['errors'] = array_merge(
                $this->processingOutput['errors'],
                $processingOutput['errors']
            );
        }

        if (!empty($processingOutput['added'])) {
            $this->processingOutput['added'] += $processingOutput['added'];
        }

        if (!empty($processingOutput['skipped'])) {
            $this->processingOutput['skipped'] += count($processingOutput['skipped']);
        }
    }

    private function processedStringOutput(): string
    {
        $processed = '';
        foreach ($this->processingOutput as $reportedHeader => $stats) {
            if (is_array($stats)) {
                $processed .= $reportedHeader.': ';

                foreach ($stats as $statHeader => $statValue) {
                    $statValue = str_replace(PHP_EOL, '', $statValue);
                    $processed .= sprintf('%s: %s. ', $statHeader, $statValue);
                }
            } else {
                $processed .= sprintf('%s %s. ', $stats, $reportedHeader);
            }
        }

        return $processed;
    }
}
