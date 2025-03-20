<?php

declare(strict_types=1);

namespace App\Command;

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

class ProcessOrgCSVCommand extends Command
{
    public static $defaultName = 'digideps:api:process-org-csv';
    private const JOB_NAME = 'org_csv_processing';

    private const CHUNK_SIZE = 50;

    private const EXPECTED_COLUMNS = [
        'Case',
        'ClientForename',
        'ClientSurname',
        'ClientDateOfBirth',
        'ClientPostcode',
        'DeputyUid',
        'DeputyType',
        'DeputyEmail',
        'DeputyOrganisation',
        'DeputyForename',
        'DeputySurname',
        'DeputyPostcode',
        'MadeDate',
        'LastReportDay',
        'ReportType',
        'OrderType',
        'Hybrid',
    ];

    protected const OPTIONAL_COLUMNS = [
        'CourtOrderUid',
        'ClientAddress1',
        'ClientAddress2',
        'ClientAddress3',
        'ClientAddress4',
        'ClientAddress5',
        'DeputyAddress1',
        'DeputyAddress2',
        'DeputyAddress3',
        'DeputyAddress4',
        'DeputyAddress5',
    ];

    private array $processingOutput = [
        'errors' => [
            'count' => 0,
            'messages' => [],
        ],
        'added' => [
            'clients' => 0,
            'deputies' => 0,
            'reports' => 0,
            'organisations' => 0,
        ],
        'updated' => [
            'clients' => 0,
            'deputies' => 0,
            'reports' => 0,
            'organisations' => 0,
        ],
        'skipped' => 0,
    ];

    private OutputInterface $cliOutput;

    public function __construct(
        private readonly S3Client $s3,
        private readonly ParameterBagInterface $params,
        private readonly LoggerInterface $verboseLogger,
        private readonly CSVDeputyshipProcessing $csvProcessing,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Processes the PA/Prof CSV Report from the S3 bucket.')
            ->addArgument('csv-filename', InputArgument::REQUIRED, 'Specify the file name of the CSV to retreive');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '1024M');
        $this->cliOutput = $output;
        $bucket = $this->params->get('s3_sirius_bucket');
        $paProReportFile = $input->getArgument('csv-filename');
        $fileLocation = sprintf('/tmp/%s', $paProReportFile);

        try {
            $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $paProReportFile,
                'SaveAs' => $fileLocation,
            ]);
        } catch (S3Exception $e) {
            if (in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES)) {
                $logMessage = 'File %s not found in bucket %s';
            } else {
                $logMessage = 'Error retrieving file %s from bucket %s';
            }
            $logMessage = sprintf($logMessage, $paProReportFile, $bucket);

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
                    '%s - success - Finished processing OrgCSV, Output: %s',
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
            return (new CsvToArray(self::EXPECTED_COLUMNS, self::OPTIONAL_COLUMNS))->create($fileName);
        } catch (\Throwable $e) {
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
                $upload = $this->csvProcessing->orgProcessing($chunk);

                $this->storeOutput($upload);
                $this->verboseLogger->notice(sprintf('Successfully processed chunk: %d', $index));
            }

            $this->verboseLogger->notice('Successfully processed all chunks');

            return true;
        }

        return false;
    }

    private function storeOutput(array $processingOutput): void
    {
        $this->processingOutput['errors']['count'] += $processingOutput['errors']['count'];
        $this->processingOutput['errors']['messages'] = implode(', ', $processingOutput['errors']['messages']);

        if (!empty($processingOutput['added'])) {
            foreach ($processingOutput['added'] as $group => $items) {
                $this->processingOutput['added'][$group] += count($items);
            }
        }

        if (!empty($processingOutput['updated'])) {
            foreach ($processingOutput['updated'] as $group => $items) {
                $this->processingOutput['updated'][$group] += count($items);
            }
        }

        if (!empty($processingOutput['skipped'])) {
            $this->processingOutput['skipped'] += $processingOutput['skipped'];
        }
    }

    private function processedStringOutput(): string
    {
        $processed = '';
        foreach ($this->processingOutput as $reportedHeader => $stats) {
            if (is_array($stats) && count($stats) >= 1) {
                foreach ($stats as $statHeader => $statValue) {
                    if (!is_array($statValue)) {
                        if ('count' === $statHeader) {
                            $processed .= sprintf('%s: %s. ', $reportedHeader, $statValue);
                        } else {
                            $processed .= sprintf('%s %s: %s. ', ucfirst($statHeader), $reportedHeader, $statValue);
                        }
                    } else {
                        if (count($statValue) >= 1) {
                            foreach ($statValue as $i => $message) {
                                if (0 === $i) {
                                    $processed .= sprintf('%s %s: ', ucfirst($statHeader), $reportedHeader);
                                }

                                $processed .= sprintf('%s; ', $message);
                            }
                        }
                    }
                }
            } else {
                $processed .= sprintf('%s %s. ', $stats, $reportedHeader);
            }
        }

        return $processed;
    }
}
