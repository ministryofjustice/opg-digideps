<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\PreRegistrationRepository;
use App\Service\DataImporter\CsvToArray;
use App\Service\File\Storage\S3Storage;
use App\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProcessLayCSVCommand extends Command
{
    public static $defaultName = 'digideps:api:process-lay-csv';

    private const JOB_NAME = 'lay_csv_processing';

    private const CHUNK_SIZE = 50;

    protected const EXPECTED_COLUMNS = [
        'Case',
        'ClientSurname',
        'DeputyUid',
        'DeputyFirstname',
        'DeputySurname',
        'DeputyAddress1',
        'DeputyAddress2',
        'DeputyAddress3',
        'DeputyAddress4',
        'DeputyAddress5',
        'DeputyPostcode',
        'ReportType',
        'MadeDate',
        'OrderType',
        'CoDeputy',
        'Hybrid',
    ];

    protected const UNEXPECTED_COLUMNS = [
        'LastReportDay',
        'DeputyOrganisation',
    ];

    private array $processingOutput = [
        'errors' => [],
        'added' => 0,
        'skipped' => 0,
    ];

    private OutputInterface $cliOutput;

    public function __construct(
        private readonly S3Client $s3,
        private readonly ParameterBagInterface $params,
        private readonly LoggerInterface $logger,
        private readonly CSVDeputyshipProcessing $csvProcessing,
        private readonly PreRegistrationRepository $preReg,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Process the Lay Deputies CSV from the S3 bucket')
            ->addArgument('csv-filename', InputArgument::REQUIRED, 'Specify the file name of the CSV to retreive');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '1024');
        $this->cliOutput = $output;
        $bucket = $this->params->get('s3_sirius_bucket');
        $layReportFile = $input->getArgument('csv-filename');
        $fileLocation = sprintf('/tmp/%s', $layReportFile);

        try {
            $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $layReportFile,
                'SaveAs' => $fileLocation,
            ]);
        } catch (S3Exception $e) {
            if (in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES)) {
                $logMessage = 'File %s not found in bucket %s';
            } else {
                $logMessage = 'Error retrieving file %s from bucket %s';
            }
            $logMessage = sprintf($logMessage, $layReportFile, $bucket);

            $this->logger->error($logMessage);
            $this->cliOutput->writeln(sprintf('%s - failure - %s', self::JOB_NAME, $logMessage));

            return Command::FAILURE;
        }

        $data = $this->csvToArray($fileLocation);
        if (count($data) >= 1 && $this->process($data)) {
            if (!unlink($fileLocation)) {
                $logMessage = sprintf('Unable to delete file %s.', $fileLocation);

                $this->logger->error($logMessage);
                $this->cliOutput->writeln(
                    sprintf(
                        '%s - failure - (partial) %s Output: %s',
                        self::JOB_NAME,
                        $logMessage,
                        $this->processedStringOutput()
                    )
                );

                return Command::SUCCESS;
            }

            if (!empty($this->processingOutput['errors'])) {
                $logMessage = sprintf('There have been soe errors');

                $this->logger->error($logMessage);
                $this->cliOutput->writeln(
                    sprintf(
                        '%s - failure - (partial) %s Output: %s',
                        self::JOB_NAME,
                        $logMessage,
                        $this->processedStringOutput()
                    )
                );

                return Command::SUCCESS;
            }

            $this->cliOutput->writeln(
                sprintf(
                    '%s - success - Finished processing LayCSV. Output: %s',
                    self::JOB_NAME,
                    $this->processedStringOutput()
                )
            );

            return Command::SUCCESS;
        }

        $this->cliOutput->writeln(
            sprintf(
                '%s - failure - %s Output: %s',
                self::JOB_NAME,
                'Process failed for unknown reason',
                $this->processedStringOutput()
            )
        );

        return Command::FAILURE;
    }

    private function csvToArray(string $fileName): array
    {
        try {
            return (new CsvToArray($fileName, false, false))
                ->setExpectedColumns(self::EXPECTED_COLUMNS)
                ->setUnexpectedColumns(self::UNEXPECTED_COLUMNS)
                ->getData();
        } catch (\RuntimeException $e) {
            $logMessage = sprintf('Error processing CSV: %s', $e->getMessage());

            $this->logger->error($logMessage);
            $this->cliOutput->writeln(self::JOB_NAME.' - failure - '.$logMessage);
        }

        return [];
    }

    private function process(mixed $data): bool
    {
        $this->preReg->deleteAll();

        if (is_array($data)) {
            $chunks = array_chunk($data, self::CHUNK_SIZE);

            foreach ($chunks as $index => $chunk) {
                $this->logger->notice(sprintf('Uploading chunk with Id: %s', $index));

                $result = $this->csvProcessing->layProcessing($chunk, $index);
                $this->storeOutput($result);
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

        $this->logger->warning('merge errors - '.count($this->processingOutput['errors']));

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
                foreach ($stats as $statHeader => $statValue) {
                    $processed .= sprintf('%s %s: %s. ', ucfirst($statHeader), $reportedHeader, $statValue);
                }
            } else {
                $processed .= sprintf('%s %s. ', $stats, $reportedHeader);
            }
        }

        return $processed;
    }
}
