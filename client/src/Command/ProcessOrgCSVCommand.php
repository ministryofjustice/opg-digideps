<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Client\RestClient;
use App\Service\CsvUploader;
use App\Service\DataImporter\CsvToArray;
use App\Service\File\Storage\FileNotFoundException;
use App\Service\File\Storage\S3Storage;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Throwable;

class ProcessOrgCSVCommand extends Command {
    protected static $defaultName = 'digideps:process-org-csv';

    private const CHUNK_SIZE = 50;

    public function __construct(
        private S3Client $s3,
        private string $siriusBucket,
        private CsvUploader $csvUploader,
        private RestClient $restClient,
        private ParameterBagInterface $params,
    )
    {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setDescription('Processes the PA/Prof CSV Report from the S3 bucket.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $output->writeln('Processing CSV');

        $bucket = $this->params->get('s3_sirius_bucket');
        $paProReportFile = 'paProDeputyReport.csv';

        try {
            $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $paProReportFile,
                'SaveAs' => '/tmp/paDeputyReport.csv'
            ]);
        } catch (S3Exception $e) {
            if (in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES)) {
                throw new FileNotFoundException("Cannot find file with reference paProDeputyReport.csv");
            }
            throw $e;
        }

        $data = $this->csvToArray('/tmp/paDeputyReport.csv');
        $this->process($data);

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
            throw new RuntimeException('Error parsing CSV file', 0, $e);
        }
    }

    private function process(mixed $data) {
        $chunks = array_chunk($data, self::CHUNK_SIZE);

        $this->processChunks($chunks);
    }

    private function processChunks($chunks)
    {
        $chunkCount = count($chunks);

        $logged = false;

        foreach ($chunks as $index => $chunk) {
            $compressedChunk = CsvUploader::compressData($chunk);

            /** @var array $upload */
            $upload = $this->restClient->post('v2/org-deputyships', $compressedChunk);

            $this->storeChunkOutput($upload);
            $this->logProgress($index + 1, $chunkCount);

            foreach ($upload['added']['organisations'] as $organisation) {
                $this->dispatchOrgCreatedEvent($organisation);
            }

            if (!$logged) {
                $this->dispatchCSVUploadEvent();
                $logged = true;
            }
        }
    }
}
