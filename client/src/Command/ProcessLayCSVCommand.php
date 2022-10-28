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
    protected static $defaultName = 'digideps:process-lay-csv';

    private const CHUNK_SIZE = 50;

    private array $output = [
        'errors' => [],
        'added' => 0,
        'skipped' => 0,
    ];

    public function __construct(
        private S3Client $s3,
        private RestClient $restClient,
        private ParameterBagInterface $params,
        private Mailer $mailer,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this
            ->setDescription('Process the Lay Deputies CSV from the S3 bucket')
            ->addArgument('email', InputArgument::REQUIRED, 'Email address to send results to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bucket = $this->params->get('s3_sirius_bucket');
        $layDeputyReportFile = 'layDeputyReport.csv';

        try {
            $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $layDeputyReportFile,
                'SaveAs' => "/tmp/layReport.csv"
            ]);
        } catch (S3Exception $e) {
            if (in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES)) {
                $this->logger->log('error', sprintf('File %s not found in bucket %s', $layDeputyReportFile, $bucket));
            }
        }

        $data = $this->csvToArray("/tmp/layReport.csv");
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
        $this->restClient->delete('/pre-registration/delete');

        $chunks = array_chunk($data, self::CHUNK_SIZE);

        foreach ($chunks as $index => $chunk) {
            $compressedChunk = CsvUploader::compressData($chunk);

            /** @var array $upload */
            $upload = $this->restClient->post('v2/lay-deputyship/upload', $compressedChunk);

            $this->storeOutput($upload);
        }

        $this->mailer->sendProcessLayCSVEmail($email, $this->output);
    }

    private function storeOutput(array $output) {
        if (!empty($output['errors'])) {
            $this->output['errors'] = array_merge($this->output['errors'], $output['errors']);
        }

        if (!empty($output['added'])) {
            $this->output['added'] += $output['added'];
        }

        if (!empty($output['skipped'])) {
            $this->output['skipped'] += $output['skipped'];
        }
    }
}
