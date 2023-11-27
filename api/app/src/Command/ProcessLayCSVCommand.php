<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\PreRegistrationRepository;
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
use Throwable;

class ProcessLayCSVCommand extends Command 
{
    protected static $defaultName = 'digideps:api:process-lay-csv';

    private const JOB_NAME = 'lay_csv_processing';

    private const CHUNK_SIZE = 50;

    private array $processingOutput = [
        'errors' => [],
        'added' => 0,
        'skipped' => 0,
    ];
    
    private OutputInterface $commandLineOutput;

    public function __construct(
        private S3Client $s3,
        private ParameterBagInterface $params,
        private LoggerInterface $logger,
        private ClientInterface $redis,
        private CSVDeputyshipProcessing $csvProcessing,
        private PreRegistrationRepository $preReg,
    ) {
        parent::__construct();
    }

    protected function configure(): void 
    {
        $this
            ->setDescription('Process the Lay Deputies CSV from the S3 bucket');
    }

    protected function execute(InputInterface $input, OutputInterface $commandLineOutput): int
    {
        $this->commandLineOutput = $commandLineOutput;
        $bucket = $this->params->get('s3_sirius_bucket');
        $layReportFile = $this->params->get('lay_report_csv_filename');
        $fileLocation = sprintf('/tmp/%s', $layReportFile);

        try {
            $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $layReportFile,
                'SaveAs' => $fileLocation
            ]);
        } catch (S3Exception $e) {
            if (in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES)) {
                $logMessage = 'File %s not found in bucket %s';
            } else {
                $logMessage = 'Error retrieving file %s from bucket %s';
            }
            $logMessage = sprintf($logMessage, $layReportFile, $bucket);

            $this->logger->error($logMessage);
            $this->commandLineOutput->writeln(sprintf('%s - failure - %s', self::JOB_NAME, $logMessage));
        }

        $data = $this->csvToArray($fileLocation);
        if ($data && $this->process($data) && empty($this->processingOutput["errors"])) {
            if (!unlink($fileLocation)) {
                $logMessage = sprintf('Unable to delete file %s.', $fileLocation);

                $this->logger->error($logMessage);
                $this->commandLineOutput->writeln(
                    sprintf(
                        '%s - partial - %s Output: %s',
                        self::JOB_NAME,
                        $logMessage,
                        implode(', ', $this->processingOutput)
                    )
                );

                return 1;
            }

            $this->commandLineOutput->writeln(
                sprintf(
                    '%s - success - Finished processing LayCSV. Output: %s',
                    self::JOB_NAME,
                    implode(', ', $this->processingOutput)
                )
            );
            return 1;
        }

        return 0;
    }

    private function csvToArray(string $fileName): array 
    {
        try {
            return (new CsvToArray($fileName, false, false))
                ->setExpectedColumns([
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
                ])
                ->setUnexpectedColumns(['LastReportDay', 'DeputyOrganisation'])
                ->getData();
        } catch (Throwable $e) {
            $logMessage = sprintf('Error processing CSV: %s', $e->getMessage());

            $this->logger->error($logMessage);
            $this->commandLineOutput->writeln(self::JOB_NAME .' - failure - '. $logMessage);
        }
    }

    private function process(mixed $data): bool 
    {
        $this->preReg->deleteAll();

        $chunks = array_chunk($data, self::CHUNK_SIZE);

        $this->redis->set('lay-csv-processing', 'processing');

        foreach ($chunks as $index => $chunk) {
            $this->logger->info(sprintf('Uploading chunk with Id: %s', $index));

            $result = $this->csvProcessing->layProcessing($chunk, $index);
            $this->storeOutput($result);
        }

        $this->redis->set('lay-csv-processing', 'completed');
        $this->redis->set('lay-csv-completed-date', date('Y-m-d H:i:s'));
        
        return true;
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
            $this->processingOutput['added'] += $processingOutput['added'];
        }

        if (!empty($processingOutput['skipped'])) {
            $this->processingOutput['skipped'] += $processingOutput['skipped'];
        }
    }
}
