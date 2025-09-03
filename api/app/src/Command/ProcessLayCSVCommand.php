<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\PreRegistrationRepository;
use App\Service\DataImporter\CsvToArray;
use App\Service\DeputyCaseService;
use App\Service\File\Storage\S3Storage;
use App\Service\LayRegistrationService;
use App\Service\UserDeputyService;
use App\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProcessLayCSVCommand extends Command
{
    public static $defaultName = 'digideps:api:process-lay-csv';

    private const JOB_NAME = 'lay_csv_processing';

    private const CHUNK_SIZE = 50;

    protected const EXPECTED_COLUMNS = [
        'Case',
        'ClientFirstname',
        'ClientSurname',
        'ClientAddress1',
        'ClientAddress2',
        'ClientAddress3',
        'ClientAddress4',
        'ClientAddress5',
        'ClientPostcode',
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

    protected const OPTIONAL_COLUMNS = [
        'CourtOrderUid',
    ];

    private array $processingOutput = [
        'added' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    private OutputInterface $cliOutput;

    public function __construct(
        private readonly S3Client $s3,
        private readonly ParameterBagInterface $params,
        private readonly LoggerInterface $verboseLogger,
        private readonly CSVDeputyshipProcessing $csvProcessing,
        private readonly PreRegistrationRepository $preReg,
        private readonly LayRegistrationService $layRegistrationService,
        private readonly DeputyCaseService $deputyCaseService,
        private readonly UserDeputyService $userDeputyService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Process the Lay Deputies CSV from the S3 bucket')
            ->addArgument('csv-filename', InputArgument::REQUIRED, 'Specify the file name of the CSV to retreive')
            ->addOption(
                'multiclient-apply-db-changes',
                null,
                InputOption::VALUE_OPTIONAL,
                'If true (default), generate multi-clients and save to database;
                if false, log multi-client changes which would be made but don\'t apply them',
                'true'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '1024M');
        $this->cliOutput = $output;
        $bucket = $this->params->get('s3_sirius_bucket');
        $layReportFile = $input->getArgument('csv-filename');
        $fileLocation = sprintf('/tmp/%s', $layReportFile);
        $multiclientApplyDbChanges = 'true' === $input->getOption('multiclient-apply-db-changes');

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

            $this->verboseLogger->error($logMessage);
            $this->cliOutput->writeln(sprintf('%s - failure - %s', self::JOB_NAME, $logMessage));

            return Command::FAILURE;
        }

        $data = $this->csvToArray($fileLocation);
        if (count($data) >= 1 && $this->process($data, $multiclientApplyDbChanges)) {
            if (!unlink($fileLocation)) {
                $logMessage = sprintf('Unable to delete file %s.', $fileLocation);

                $this->verboseLogger->error($logMessage);
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
        } catch (\Exception $e) {
            $logMessage = sprintf('Error processing CSV: %s', $e->getMessage());

            $this->verboseLogger->error($logMessage);
            $this->cliOutput->writeln(self::JOB_NAME.' - failure - '.$logMessage);
        }

        return [];
    }

    // this returns true if processing succeeds, even if there were failures or exceptions;
    // it only returns false if the data to be processed is not an array
    private function process(mixed $data, bool $multiclientApplyDbChanges = true): bool
    {
        $this->preReg->deleteAll();

        if (!is_array($data)) {
            return false;
        }

        $chunks = array_chunk($data, self::CHUNK_SIZE);

        // lay CSV baseline processing
        foreach ($chunks as $index => $chunk) {
            $this->verboseLogger->notice(sprintf('Uploading chunk with Id: %s', $index));

            $result = $this->csvProcessing->layProcessing($chunk, $index);
            $this->storeOutput($result);
        }

        // additional multi-client processing
        $this->verboseLogger->notice('Directly creating any new Lay clients for active deputies');
        $result = $this->csvProcessing->layProcessingHandleNewMultiClients($multiclientApplyDbChanges);

        if (!$multiclientApplyDbChanges) {
            $this->verboseLogger->notice(
                'MULTI-CLIENT CHANGES: '.json_encode($result)
            );
        }

        if (0 == $result['new-clients-found']) {
            $this->verboseLogger->notice('No new multiclients were found, so none were added');
        }

        // ensure that all active clients have at least one report associated with them,
        // to fix issues caused by partially-registered users (see DDLS-911)
        $this->verboseLogger->notice('Adding missing reports to clients');
        try {
            $numReportsAdded = $this->layRegistrationService->addMissingReports();
            $this->verboseLogger->notice("Added $numReportsAdded missing reports to clients");
        } catch (\Exception $e) {
            $this->verboseLogger->error('Error encountered while adding missing reports: '.$e->getMessage());
            $this->verboseLogger->error($e->getTraceAsString());
        }

        // additional deputy_case association patching (see DDLS-907)
        $this->verboseLogger->notice('Fixing missing deputy_case associations');
        try {
            $numDeputyCaseAssociationsAdded = $this->deputyCaseService->addMissingDeputyCaseAssociations();
            $this->verboseLogger->notice("Added $numDeputyCaseAssociationsAdded deputy_case associations");
        } catch (\Exception $e) {
            $this->verboseLogger->error('Error encountered while fixing deputy_case associations: '.$e->getMessage());
            $this->verboseLogger->error($e->getTraceAsString());
        }

        // create deputies where missing, and associate users with deputies where they don't have one
        $this->verboseLogger->notice('Adding deputies to users where they are missing');
        try {
            $numUserDeputyAssociations = $this->userDeputyService->addMissingUserDeputies();
            $this->verboseLogger->notice("Added $numUserDeputyAssociations user <-> deputy associations");
        } catch (\Exception $e) {
            $this->verboseLogger->error('Error encountered while adding user <-> deputy associations: '.$e->getMessage());
        }

        return true;
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
