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
use Predis\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProcessOrgCSVCommand extends Command
{
    protected static $defaultName = 'digideps:process-org-csv';

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
        'CourtOrderUid',
    ];

    private const UNEXPECTED_COLUMNS = [
        'NDR',
    ];

    private array $output = [
        'errors' => [],
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

    public function __construct(
        private S3Client $s3,
        private RestClient $restClient,
        private ParameterBagInterface $params,
        private Mailer $mailer,
        private LoggerInterface $logger,
        private ClientInterface $redis,
        private string $workspace
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Processes the PA/Prof CSV Report from the S3 bucket.')
            ->addArgument('email', InputArgument::REQUIRED, 'Email address to send results to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
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
                $this->logger->error(sprintf('File %s not found in bucket %s', $paProReportFile, $bucket));
            } else {
                $this->logger->error(
                    sprintf(
                        'Error retrieving file %s from bucket %s',
                        $paProReportFile,
                        $bucket
                    )
                );
            }
        }

        $data = $this->csvToArray($fileLocation);

        if ($this->process($data, $input->getArgument('email')) && empty($this->output['errors'])) {
            if (!unlink($fileLocation)) {
                $this->logger->error('Unable to delete file /tmp/orgReport.csv.');
            }

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    private function csvToArray(string $fileName): array
    {
        try {
            return (new CsvToArray($fileName, false))
            ->setExpectedColumns(self::EXPECTED_COLUMNS)
            ->setOptionalColumns(self::OPTIONAL_COLUMNS)
            ->setUnexpectedColumns(self::UNEXPECTED_COLUMNS)
            ->getData();
        } catch (\Throwable $e) {
            $this->logger->error(sprintf('Error processing CSV: %s', $e->getMessage()));
        }

        return [];
    }

    private function process(mixed $data, string $email): bool
    {
        $chunks = array_chunk($data, self::CHUNK_SIZE);

        foreach ($chunks as $index => $chunk) {
            try {
                $compressedChunk = CsvUploader::compressData($chunk);

                /** @var array $upload */
                $upload = $this->restClient->setTimeout(60)->post('v2/org-deputyships', $compressedChunk);

                $this->storeOutput($upload);

                $this->logger->notice(sprintf('Successfully processed chunk: %d', $index));
            } catch (\Throwable $e) {
                $this->logger->error(sprintf('Error processing chunk: %d, error: %s', $index, $e->getMessage()));
            }
        }

        $this->logger->notice('Successfully processed all chunks');

        return $this->mailer->sendProcessOrgCSVEmail($email, $this->output);
    }

    private function storeOutput(array $output): void
    {
        $this->output['errors']['count'] += $output['errors']['count'];
        $this->output['errors']['messages'] = implode(', ', $output['errors']['messages']);

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
