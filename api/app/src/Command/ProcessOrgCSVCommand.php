<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DataImporter\CsvToArray;
use App\Service\File\Storage\S3Storage;
use App\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Predis\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
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
    
    private const OPTIONAL_COLUMNS = [
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
    
    private const UNEXPECTED_COLUMNS = [
        'NDR'
    ];
    
    private array $processingOutput = [
        'errors' => [],
        'added' => [
            'clients' => 0,
            'named_deputies' => 0,
            'reports' => 0,
            'organisations' => 0,
        ],
        'updated' => [
            'clients' => 0,
            'named_deputies' => 0,
            'reports' => 0,
            'organisations' => 0,
        ],
        'skipped' => 0,
    ];

    private OutputInterface $cliOutput;

    public function __construct(
        private S3Client $s3,
        private ParameterBagInterface $params,
        private LoggerInterface $logger,
        private ClientInterface $redis,
        private CSVDeputyshipProcessing $csvProcessing,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Processes the PA/Prof CSV Report from the S3 bucket.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cliOutput = $output;
        $bucket = $this->params->get('s3_sirius_bucket');
        $paProReportFile = $this->params->get('pa_pro_report_csv_filename');
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
            
            $this->logger->error($logMessage);
            $this->cliOutput->writeln(sprintf('%s - failure - %s', self::JOB_NAME, $logMessage));
            
            return Command::FAILURE;
        }

        $data = $this->csvToArray($fileLocation);
        if (count($data) >= 1 && $this->process($data) && empty($this->processingOutput['errors'])) {
            if (!unlink($fileLocation)) {
                $logMessage = sprintf('Unable to delete file %s', $fileLocation);

                $this->logger->error($logMessage);
                $this->cliOutput->writeln(
                    sprintf(
                        '%s - partial - %s processing Output: %s',
                        self::JOB_NAME,
                        $logMessage,
                        $this->processedStringOutput()
                    )
                );
                
                return Command::SUCCESS;
            }

            $this->cliOutput->writeln(
                sprintf(
                    '%s - success - Finished processing OrgCSV. Output: %s', 
                    self::JOB_NAME, 
                    $this->processedStringOutput()
                )
            );
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    private function csvToArray(string $fileName)
    {
        try {
            return (new CsvToArray($fileName, false))
            ->setExpectedColumns(self::EXPECTED_COLUMNS)
            ->setOptionalColumns(self::OPTIONAL_COLUMNS)
            ->setUnexpectedColumns(self::UNEXPECTED_COLUMNS)
            ->getData();
        } catch (\Throwable $e) {
            $logMessage = sprintf('Error processing CSV: %s', $e->getMessage());

            $this->logger->error($logMessage);
            $this->cliOutput->writeln(self::JOB_NAME .' - failure - '. $logMessage);
        }
        
        return [];
    }

    private function process(mixed $data): bool
    {
        if (is_array($data)) {
            $chunks = array_chunk($data, self::CHUNK_SIZE);

            $this->redis->set('org-csv-processing', 'processing');
            foreach ($chunks as $index => $chunk) {
                $upload = $this->csvProcessing->orgProcessing($chunk);

                $this->storeOutput($upload);

                $this->logger->info(sprintf('Successfully processed chunk: %d', $index));
            }

            $this->logger->info('Successfully processed all chunks');

            $this->redis->set('org-csv-processing', 'completed');
            $this->redis->set('org-csv-completed-date', date('Y-m-d H:i:s'));

            return true;
        }
        
        return false;
    }

    private function storeOutput(array $processingOutput)
    {
        if (!empty($processingOutput['errors'])) {
            $this->processingOutput['errors'] = array_merge(
                $this->processingOutput['errors'], 
                $processingOutput['errors']
            );
        }

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

    private function processedStringOutput()
    {
        $processed = "";
        foreach ($this->processingOutput as $reportedHeader => $stats ) {
            if (is_array($stats) && count($stats) >= 1) {
                foreach ($stats as $statHeader => $statValue ) {
                    $processed .= sprintf("%s %s: %s. ", ucfirst($statHeader), $reportedHeader, $statValue);
                }
            } else {
                $processed .= sprintf("%s %s. ", $stats, $reportedHeader);
            }
        }

        return $processed;
    }
}
