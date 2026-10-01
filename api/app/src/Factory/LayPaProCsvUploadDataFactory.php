<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Factory;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Common\Deputy\DeputyType;
use OPG\Digideps\Backend\Entity\Staging\StagingLayIngest;
use OPG\Digideps\Backend\Entity\Staging\StagingPaProIngest;
use OPG\Digideps\Backend\Service\DataImporter\CsvToArray;
use OPG\Digideps\Backend\Service\File\Storage\S3Storage;
use OPG\Digideps\Common\Validating\ValidatingArray;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class LayPaProCsvUploadDataFactory implements DataFactoryInterface
{
    private const array LAY_EXPECTED_COLUMNS = [
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
        'OrderType'
    ];

    private const array PRO_PA_EXPECTED_COLUMNS = [
        'Case',
        'ClientForename',
        'ClientSurname',
        'ClientDateOfBirth',
        'ClientAddress1',
        'ClientAddress2',
        'ClientAddress3',
        'ClientAddress4',
        'ClientAddress5',
        'ClientPostcode',
        'DeputyType',
        'DeputyUid',
        'DeputyEmail',
        'DeputyOrganisation',
        'DeputyForename',
        'DeputySurname',
        'DeputyAddress1',
        'DeputyAddress2',
        'DeputyAddress3',
        'DeputyAddress4',
        'DeputyAddress5',
        'DeputyPostcode',
        'MadeDate',
        'ReportType',
        'OrderType',
    ];

    private const array OPTIONAL_COLUMNS = [
        'CourtOrderUid',
        'Hybrid',
        'CoDeputy',
        'LastReportDay',
    ];

    private readonly bool $production;
    /**
     * @var array<int, string> $errors
     */
    private array $errors;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $parameters,
        private readonly S3Client $s3,
    ) {
        $this->production = $this->parameters->get('workspace') === 'production';
        $this->errors = [];
    }

    public function getName(): string
    {
        return 'LayPaProCsvUpload';
    }

    public function run(bool $dryRun): DataFactoryResult
    {
        try {
            $layCount = $paCount = $proCount = 0;

            $localPaths = $this->fetchFromAWS();
            if ($localPaths !== null) {
                ['layPath' => $layPath, 'proPaPath' => $proPaPath] = $localPaths;
                $layCount = $this->runLay($layPath, $dryRun);
                $this->deleteLocalFile($layPath);
                ['paCount' => $paCount, 'proCount' => $proCount] = $this->runPaPro($proPaPath, $dryRun);
                $this->deleteLocalFile($proPaPath);
            }
        } catch (\Throwable $e) {
            $this->errors[] = "Unexpected exception during LayPaProCsvUpload: {$e->getMessage()}";
        }

        return new DataFactoryResult([
            'success' => [
                "Read in {$layCount} LAY deputies",
                "Read in {$paCount} PA deputies",
                "Read in {$proCount} PRO deputies",
            ],
            'errors' => $this->errors,
        ]);
    }

    /**
     * @return array{'layPath': string, 'proPaPath': string}|null
     */
    private function fetchFromAWS(): ?array
    {
        $paProAWS = $this->parameters->get('pa_pro_report_csv_filename');
        $layAWS = $this->parameters->get('lay_report_csv_filename');
        if (!is_string($paProAWS) || $paProAWS === '' || !is_string($layAWS) || $layAWS === '') {
            $this->errors[] = "The CSV paths of the ay and PaPro ingest are not properly configured!";
            return null;
        }
        $paProLocalPath = $this->copyFromAWS($paProAWS);
        $layLocalPath = $this->copyFromAWS($layAWS);
        if ($paProLocalPath === null || $layLocalPath === null) {
            if ($layLocalPath !== null) {
                unlink($layLocalPath);
            }
            if ($paProLocalPath !== null) {
                unlink($paProLocalPath);
            }
            return null;
        }
        return [
            'layPath' => $layLocalPath,
            'proPaPath' => $paProLocalPath,
        ];
    }

    private function copyFromAWS(string $awsPath): ?string
    {
        $bucket = $this->parameters->get('s3_sirius_bucket');
        if (!is_string($bucket)) {
            $this->errors[] = "AWS bucket not configured or configured in an unexpected way.";
            return null;
        }
        $localPath = "/tmp/{$awsPath}";
        if (file_exists($localPath)) {
            unlink($localPath);
        }

        try {
            $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $awsPath,
                'SaveAs' => $localPath,
            ]);
        } catch (S3Exception $e) {
            if (in_array($e->getAwsErrorCode(), S3Storage::MISSING_FILE_AWS_ERROR_CODES)) {
                $this->errors[] = "File {$awsPath} not found in bucket {$bucket}";
            } else {
                $this->errors[] = "Error getting file {$awsPath} from bucket {$bucket}: {$e->getMessage()}";
            }
            return null;
        }

        return $localPath;
    }

    /**
     * @return \Generator<int, StagingLayIngest, void, void>
     */
    private function readLayCsv(string $path): \Generator
    {
        try {
            $reader = new CsvToArray(self::LAY_EXPECTED_COLUMNS, self::OPTIONAL_COLUMNS);
            foreach ($reader->createAsIterator($path) as $row => $item) {
                $item = new ValidatingArray($item);
                $case = $item->getStringOrThrow('Case');
                $deputyUid = $item->getStringOrThrow('DeputyUid');
                try {
                    yield new StagingLayIngest(
                        $this->validate($case, 20),
                        $this->process($this->validate($item->getStringOrThrow('ClientFirstname'), 50)),
                        $this->process($this->validate($item->getStringOrThrow('ClientSurname'), 50)),
                        $this->process($this->validate($item->getStringOrDefault('ClientAddress1', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('ClientAddress2', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('ClientAddress3', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('ClientAddress4', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('ClientAddress5', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('ClientPostcode', ''), 10), 'W1'),
                        $this->validate($deputyUid, 20),
                        $this->process($this->validate($item->getStringOrThrow('DeputyFirstname'), 100)),
                        $this->process($this->validate($item->getStringOrThrow('DeputySurname'), 100)),
                        $this->process($this->validate($item->getStringOrDefault('DeputyAddress1', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('DeputyAddress2', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('DeputyAddress3', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('DeputyAddress4', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('DeputyAddress5', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('DeputyPostcode', ''), 10), 'W2'),
                        CourtOrderReportType::from(strtoupper($item->getStringOrThrow('ReportType'))),
                        new \DateTimeImmutable($item->getStringOrThrow('MadeDate')),
                        CourtOrderType::from(strtolower($item->getStringOrThrow('OrderType'))),
                    );
                } catch (\Throwable $e) {
                    $this->errors[] = "Error processing lay CSV row {$row} - {$case} - {$deputyUid}: {$e->getMessage()}";
                }
            }
        } catch (\Throwable $e) {
            $this->errors[] = "Error processing lay CSV: {$e->getMessage()}";
        }
    }


    /**
     * @return \Generator<int, StagingPaProIngest, void, void>
     */
    private function readProPaCsv(string $path): \Generator
    {
        try {
            $reader = new CsvToArray(self::PRO_PA_EXPECTED_COLUMNS, self::OPTIONAL_COLUMNS);
            foreach ($reader->createAsIterator($path) as $row => $item) {
                $item = new ValidatingArray($item);
                $case = $item->getStringOrThrow('Case');
                $deputyUid = $item->getStringOrThrow('DeputyUid');
                try {
                    yield new StagingPaProIngest(
                        $this->validate($case, 20),
                        $this->process($this->validate($item->getStringOrThrow('ClientForename'), 50)),
                        $this->process($this->validate($item->getStringOrThrow('ClientSurname'), 50)),
                        new \DateTimeImmutable($this->process($item->getStringOrDefault('ClientDateOfBirth', ''), '1953-06-02')),
                        $this->process($this->validate($item->getStringOrDefault('ClientAddress1', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('ClientAddress2', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('ClientAddress3', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('ClientAddress4', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('ClientAddress5', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('ClientPostcode', ''), 10), 'W1'),
                        DeputyType::from(strtoupper($item->getStringOrThrow('DeputyType'))),
                        $this->validate($deputyUid, 20),
                        $this->process($this->validate($item->getStringOrThrow('DeputyEmail'), 60), email: true),
                        $this->validate($item->getStringOrDefault('DeputyOrganisation', ''), 100),
                        $this->process($this->validate($item->getStringOrThrow('DeputyForename'), 100)),
                        $this->process($this->validate($item->getStringOrThrow('DeputySurname'), 100)),
                        $this->process($this->validate($item->getStringOrDefault('DeputyAddress1', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('DeputyAddress2', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('DeputyAddress3', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('DeputyAddress4', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('DeputyAddress5', ''), 200)),
                        $this->process($this->validate($item->getStringOrDefault('DeputyPostcode', ''), 10), 'W2'),
                        new \DateTimeImmutable($item->getStringOrThrow('MadeDate')),
                        CourtOrderReportType::from(strtoupper($item->getStringOrThrow('ReportType'))),
                        CourtOrderType::from(strtolower($item->getStringOrThrow('OrderType'))),
                    );
                } catch (\Throwable $e) {
                    $this->errors[] = "Error processing pa-pro CSV row {$row} - {$case} - {$deputyUid}: {$e->getMessage()}";
                }
            }
        } catch (\Throwable $e) {
            $this->errors[] = "Error processing pa-pro CSV: {$e->getMessage()}";
        }
    }

    private function validate(string $value, int $length): string
    {
        if (strlen($value) > $length) {
            throw new \DomainException("Value was expected to be at most {$length} characters long.");
        }
        return $value;
    }

    private function process(string $value, ?string $static = null, bool $email = false): string
    {
        if (!$this->production && $value !== '') {
            if ($static !== null) {
                $value = $static;
            } elseif ($email && str_contains($value, '@')) {
                [$user, $domain] = explode('@', $value, 2);
                $user = substr($this->anonymise($user), 0, 59 - strlen($domain));
                $value = "{$user}@{$domain}";
            } else {
                $value = $this->anonymise($value);
                $value[0] = strtoupper($value[0]);
            }
        }

        return $value;
    }

    private function anonymise(string $value): string
    {
        return str_replace(
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            ['g', 'h', 'k', 'l', 'm', 'p', 'q', 'r', 's', 't'],
            hash('md5', hash('sha512', $value))
        );
    }

    private function runLay(string $layPath, bool $dryRun): int
    {
        $this->truncate(StagingLayIngest::class);

        $layCount = 0;
        foreach ($this->readLayCsv($layPath) as $lay) {
            $layCount++;
            if (!$dryRun) {
                $this->entityManager->persist($lay);
                if ($layCount % 128 === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
            }
        }

        if (!$dryRun) {
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        return $layCount;
    }

    /**
     * @return array{'paCount': int, 'proCount': int}
     */
    private function runPaPro(string $proPaPath, bool $dryRun): array
    {
        $this->truncate(StagingPaProIngest::class);

        $paCount = 0;
        $proCount = 0;

        foreach ($this->readProPaCsv($proPaPath) as $paPro) {
            if ($paPro->deputyType === DeputyType::PA) {
                $paCount++;
            } else {
                $proCount++;
            }
            if (!$dryRun) {
                $this->entityManager->persist($paPro);
                if (($paCount + $proCount) % 128 === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
            }
        }

        if (!$dryRun) {
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        return ['paCount' => $paCount, 'proCount' => $proCount];
    }

    private function truncate(string $className): void
    {
        $this->entityManager->beginTransaction();
        $this->entityManager->createQuery("DELETE FROM {$className} cn")->execute();
        $this->entityManager->flush();
        $this->entityManager->commit();
    }

    private function deleteLocalFile(string $path): void
    {
        if (!unlink($path)) {
            $this->errors[] = "Could not unlink {$path}";
        }
    }
}
