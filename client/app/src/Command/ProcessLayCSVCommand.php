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

class ProcessLayCSVCommand extends Command
{
    protected static $defaultName = 'digideps:process-lay-csv';

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

    private const OPTIONAL_COLUMNS = [
        'CourtOrderUid'
    ];

    protected const UNEXPECTED_COLUMNS = [
        'LastReportDay',
        'DeputyOrganisation',
    ];

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
        private LoggerInterface $verboseLogger,
        private ClientInterface $redis,
        private string $workspace
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Process the Lay Deputies CSV from the S3 bucket')
            ->addArgument('email', InputArgument::REQUIRED, 'Email address to send results to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bucket = $this->params->get('s3_sirius_bucket');
        $layReportFile = $this->params->get('lay_report_csv_filename');
        $fileLocation = sprintf('/tmp/%s', $layReportFile);

        try {
            $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $layReportFile,
                'SaveAs' => $fileLocation,
            ]);
        } catch (S3Exception $e) {
            if (in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES)) {
                $this->verboseLogger->error(sprintf('File %s not found in bucket %s', $layReportFile, $bucket));
            } else {
                $this->verboseLogger->error(
                    sprintf(
                        'Error getting file %s from bucket %s: %s',
                        $layReportFile,
                        $bucket,
                        $e->getMessage()
                    )
                );
            }
        }

        $data = $this->csvToArray($fileLocation);
        if ($this->process($data, $input->getArgument('email')) && empty($this->output['errors'])) {
            if (!unlink($fileLocation)) {
                $this->verboseLogger->error(sprintf('Unable to delete file %s.', $layReportFile));
            }

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    private function csvToArray(string $fileName): array
    {
        try {
            return (new CsvToArray($fileName, false, false))
                ->setExpectedColumns(self::EXPECTED_COLUMNS)
                ->setOptionalColumns(self::OPTIONAL_COLUMNS)
                ->setUnexpectedColumns(self::UNEXPECTED_COLUMNS)
                ->getData();
        } catch (Throwable $e) {
            $this->verboseLogger->error(sprintf('Error processing CSV file: %s', $e->getMessage()));
        }

        return [];
    }

    private function process(mixed $data, string $email): void
    {
        $this->restClient->delete('/pre-registration/delete');

        $chunks = array_chunk($data, self::CHUNK_SIZE);

        $this->redis->set($this->workspace.'-lay-csv-processing', 'processing');

        foreach ($chunks as $index => $chunk) {
            $compressedChunk = CsvUploader::compressData($chunk);

            /** @var array $upload */
            $upload = $this->restClient->post('v2/lay-deputyship/upload', $compressedChunk);

            $this->storeOutput($upload);
        }

        $this->mailer->sendProcessLayCSVEmail($email, $this->output);
    }

    private function storeOutput(array $output): void
    {
        if (!empty($output['errors'])) {
            $this->output['errors'] = array_merge(
                $this->output['errors'],
                $output['errors']
            );
        }

        if (!empty($output['added'])) {
            $this->output['added'] += $output['added'];
        }

        if (!empty($output['skipped'])) {
            $this->output['skipped'] += count($output['skipped']);
        }
    }
}
