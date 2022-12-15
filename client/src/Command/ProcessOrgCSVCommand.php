<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Client\RestClient;
use App\Service\CsvUploader;
use App\Service\DataImporter\CsvToArray;
use App\Service\File\Storage\S3Storage;
use App\Service\Mailer\Mailer;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use DateTime;
use Predis\Client;
use Predis\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Throwable;

class ProcessOrgCSVCommand extends Command {
    protected static $defaultName = 'digideps:process-org-csv';

    private const CHUNK_SIZE = 50;

    private array $output = [
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

    public function __construct(
        private S3Client $s3,
        private RestClient $restClient,
        private ParameterBagInterface $params,
        private Mailer $mailer,
        private LoggerInterface $logger,
        private ClientInterface $redis,
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this
            ->setDescription('Processes the PA/Prof CSV Report from the S3 bucket.')
            ->addArgument('email', InputArgument::REQUIRED, 'Email address to send results to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $bucket = $this->params->get('s3_sirius_bucket');
        $paProReportFile = $this->params->get('pa_pro_report_csv_filename');

        try {
            $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $paProReportFile,
                'SaveAs' => "/tmp/orgReport.csv"
            ]);
        } catch (S3Exception $e) {
            if (in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES)) {
                $this->logger->log('error', sprintf('File %s not found in bucket %s', $paProReportFile, $bucket));
            } else {
                $this->logger->log('error', sprintf('Error retrieving file %s from bucket %s', $paProReportFile, $bucket));
            }
        }

        $data = $this->csvToArray("/tmp/orgReport.csv");
        $this->process($data, $input->getArgument('email'));

        if (!unlink("/tmp/orgReport.csv")) {
            $this->logger->log('error', 'Unable to delete file /tmp/orgReport.csv.');
        }

        return 0;
    }

    private function csvToArray(string $fileName) {
        try {
            return (new CsvToArray($fileName, false))
            ->setExpectedColumns([
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
            ])
            ->setOptionalColumns([
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
            ])
            ->setUnexpectedColumns([
                'NDR',
            ])
            ->getData();
        } catch (Throwable $e) {
            $this->logger->log('error', sprintf('Error processing CSV: %s', $e->getMessage()));
        }
    }

    private function process(mixed $data, string $email) {
        $chunks = array_chunk($data, self::CHUNK_SIZE);

        $this->redis->set('org-csv-processing', 'processing');

        foreach ($chunks as $index => $chunk) {
            $compressedChunk = CsvUploader::compressData($chunk);

            /** @var array $upload */
            $upload = $this->restClient->post('v2/org-deputyships', $compressedChunk);

            $this->storeOutput($upload);
        }

        $this->redis->set('org-csv-processing', 'completed');
        $this->redis->set('org-csv-completed-date', date('Y-m-d H:i:s'));

        if ($email !== NULL) {
            $this->mailer->sendProcessOrgCSVEmail($email, $this->output);
        } else {
            $this->mailer->sendProcessOrgCSVEmail($this->emailParams['csv_send_to_address'], $this->output);
        }
    }

    private function storeOutput(array $output) {
        if (!empty($output['errors'])) {
            $this->output['errors'] = array_merge($this->output['errors'], $output['errors']);
        }

        if (!empty($output['added'])) {
            foreach ($output['added'] as $group => $items) {
                $this->output['added'][$group] += count($items);
            }
        }

        if (!empty($output['updated'])) {
            foreach ($output['updated'] as $group => $items) {
                $this->output['updated'][$group] += count($items);
            }
        }

        if (!empty($output['skipped'])) {
            $this->output['skipped'] += $output['skipped'];
        }
    }
}
