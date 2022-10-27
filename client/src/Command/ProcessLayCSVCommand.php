<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Client\Internal\LayDeputyshipApi;
use App\Service\Client\Internal\PreRegistrationApi;
use App\Service\Client\RestClient;
use App\Service\CsvUploader;
use App\Service\DataImporter\CsvToArray;
use App\Service\File\Storage\S3Storage;
use App\Service\Mailer\Mailer;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Throwable;

class ProcessLayCSVCommand extends Command {
    protected static $defaultName = 'digideps:process-org-csv';

    private const CHUNK_SIZE = 2000;

    private array $output = [
        'errors' => [],
        'added' => 0,
        'skipped' => 0,
    ];

    public function __construct(
        private S3Client $s3,
        private CsvUploader $csvUploader,
        private RestClient $restClient,
        private ParameterBagInterface $params,
        private Mailer $mailer,
        private LoggerInterface $logger,
        private LayDeputyshipApi $layApi,
        private PreRegistrationApi $preRegApi,
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this
            ->setDescription('Process the Lay Deputies CSV from the S3 bucket')
            ->addArgument('email', InputArgument::REQUIRED, 'Email address to send results to');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bucket = $this->params->get('s3_sirius_bucket');
        $layDeputyReportFile = 'layDeputyReport.csv';

        try {
            $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $layDeputyReportFile,
                'SaveAs' => "/tmp/$layDeputyReportFile"
            ]);
        } catch (S3Exception $e) {
            if (in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES)) {
                $this->logger->log('error', sprintf('File %s not found in bucket %s', $layDeputyReportFile, $bucket));
            }
        }

        $data = $this->csvToArray('/tmp/layDeputyReport.csv');
        $this->process($data, $input->getArgument('email'));

        return 0;
    }

    private function csvToArray(string $fileName) {
        try {
            return (new CsvToArray($fileName, false, false))
                ->setOptionalColumns([
                    'Case',
                    'ClientSurname',
                    'DeputyUid',
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
                ])
                ->setUnexpectedColumns(['LastReportDay', 'DeputyOrganisation'])
                ->getData();
        } catch (Throwable $e) {
            $this->logger->log('error', sprintf('Error processing CSV file: %s', $e->getMessage()));
        }
    }

    private function process(mixed $data, string $email) {
        $chunks = array_chunk($data, self::CHUNK_SIZE);

        if (count($data) < self::CHUNK_SIZE) {
            $compressedData = CsvUploader::compressData($data);
            $this->preRegApi->deleteAll();

            $response = $this->layApi->uploadLayDeputyShip($compressedData, "below_2000_rows");
            $this->storeOutput($response);

            foreach ($response['errors'] as $err) {
                $this->logger->warning(
                    sprintf('Error while uploading csv: %s', $err)
                );
            }
        }
    }

    private function storeOutput(array $response) {
        $this->output['added'] += $response['added'];
        $this->output['skipped'] += count($response['skipped']);

        $this->output['errors'] = array_merge($this->output['errors'], $response['errors']);
    }
}
