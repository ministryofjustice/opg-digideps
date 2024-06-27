<?php

namespace App\Tests\Unit\Command;

use App\Repository\PreRegistrationRepository;
use App\Service\DataImporter\CsvToArray;
use App\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\Result;
use Mockery as Mock;
use PhpParser\Node\Arg;
use Predis\ClientInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\ProcessLayCSVCommand;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProcessLayCSVCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        # TODO Refactor CSV Process so we can mock this properly
        copy(dirname(dirname(__DIR__)) .'/csv/layDeputyReport.csv', '/tmp/layDeputyReport.csv');

        $this->s3 = \Mockery::mock(S3Client::class);
        $this->params = \Mockery::mock(ParameterBagInterface::class);
        $this->params->shouldReceive('get')
            ->with('s3_sirius_bucket')
            ->andReturn('bucket');
        
        $this->csvFilename = 'layDeputyReport.csv';

        $this->logger = \Mockery::mock(LoggerInterface::class);
        $this->preReg = \Mockery::mock(PreRegistrationRepository::class);
        $this->csvProcessing = \Mockery::mock(CSVDeputyshipProcessing::class);
        
        $this->csvArray = Mock::mock(CsvToArray::class);

        $setUp = new ProcessLayCSVCommand(
            $this->s3,
            $this->params,
            $this->logger,
            $this->csvProcessing, 
            $this->preReg
        );

        $app->add($setUp);

        $command = $app->find(ProcessLayCSVCommand::$defaultName);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithSuccessfulFilePull(): void
    {
        $this->s3->shouldReceive('getObject')
            ->with([
                'Bucket' => 'bucket',
                'Key' => $this->csvFilename,
                'SaveAs' => '/tmp/'. $this->csvFilename,
            ])
            ->andReturn(new Result());
        
        $this->preReg->shouldReceive('deleteAll');
        
        $this->logger->shouldReceive('notice')
            ->with('Uploading chunk with Id: 0');
        
        $this->csvProcessing->shouldReceive('layProcessing')
            ->with([
                [
                    'Case' => '98989898',
                    'ClientSurname' => 'SMITHY',
                    'DeputyUid' => '19371937',
                    'DeputyFirstname' => 'JOHN',
                    'DeputySurname' => 'DUCK',
                    'DeputyAddress1' => '2 London Road',
                    'DeputyAddress2' => 'Padstow',
                    'DeputyAddress3' => 'Brentwood',
                    'DeputyAddress4' => 'Cornwall',
                    'DeputyAddress5' => '',
                    'DeputyPostcode' => 'B1',
                    'ReportType' => 'OPG102',
                    'MadeDate' => '2011-04-14',
                    'OrderType' => 'pfa',
                    'CoDeputy' => 'no',
                    'Hybrid' => 'SINGLE',
                    'CourtOrderUid' => 70057777001,
                ],
                    [
                        'Case' => '97979797',
                        'ClientSurname' => 'GAVIN',
                        'DeputyUid' => '19371938',
                        'DeputyFirstname' => 'ESTER',
                        'DeputySurname' => 'VANDERQUACK',
                        'DeputyAddress1' => 'Unique Epoxy',
                        'DeputyAddress2' => '784 Juno St',
                        'DeputyAddress3' => 'Oakham',
                        'DeputyAddress4' => 'North Horsham',
                        'DeputyAddress5' => 'West Sussex',
                        'DeputyPostcode' => 'SW1',
                        'ReportType' => 'OPG102',
                        'MadeDate' => '2011-04-15',
                        'OrderType' => 'pfa',
                        'CoDeputy' => 'no',
                        'Hybrid' => 'SINGLE',
                        'CourtOrderUid' => 70057777002,
                    ],
                    [
                        'Case' => '9696969T',
                        'ClientSurname' => 'PATRICK',
                        'DeputyUid' => '19371939',
                        'DeputyFirstname' => 'VICKY',
                        'DeputySurname' => 'MCDUCK',
                        'DeputyAddress1' => 'Shieldaig',
                        'DeputyAddress2' => 'Northamptonshire',
                        'DeputyAddress3' => '',
                        'DeputyAddress4' => '',
                        'DeputyAddress5' => '',
                        'DeputyPostcode' => 'B73',
                        'ReportType' => 'OPG102',
                        'MadeDate' => '2011-04-16',
                        'OrderType' => 'pfa',
                        'CoDeputy' => 'no',
                        'Hybrid' => 'SINGLE',
                        'CourtOrderUid' => 70057777003,
                    ]
                ], 0)
            ->andReturn([
                'added' => 3,
                'errors' => 0,
                'report_update_count' => 0,
                'cases_with_updated_reports' => 0,
                'source' => 'sirius',
                'court_orders' => [70057777001,70057777002,70057777003]
            ]);

        $this->csvProcessing->shouldReceive('courtOrdersActiveSwitch')
            ->with([70057777001,70057777002,70057777003]);

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $this->commandTester->assertCommandIsSuccessful();
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            'lay_csv_processing - success - Finished processing LayCSV. Output:', 
            $output
        );
    }

    public function testExecuteWithFailedFilePullS3Error(): void
    {
        $s3Exception = \Mockery::mock(s3Exception::class);
        $s3Exception->shouldReceive('getAwsErrorCode')
            ->andReturn('No');
        
        $this->s3->shouldReceive('getObject')
            ->with([
                'Bucket' => 'bucket',
                'Key' => $this->csvFilename,
                'SaveAs' => '/tmp/'. $this->csvFilename,
            ])
        ->andThrows($s3Exception);
        
        $this->logger->shouldReceive('error')
            ->with('Error retrieving file layDeputyReport.csv from bucket bucket');

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
    'lay_csv_processing - failure - Error retrieving file layDeputyReport.csv from bucket',
            $output
        );
    }
    
    public function testExecuteWithMissingCSVCol(): void
    {
        # Required so we can trigger missing column exception with bad file
        copy(dirname(dirname(__DIR__)) .'/csv/layDeputyReport-bad.csv', '/tmp/layDeputyReport.csv');
        $mockError = new \RuntimeException('Invalid file. Cannot find expected header');

        $this->s3->shouldReceive('getObject')
            ->with([
                'Bucket' => 'bucket',
                'Key' => $this->csvFilename,
                'SaveAs' => '/tmp/'. $this->csvFilename,
            ])
            ->andReturn(new Result());

        $this->csvArray->shouldReceive(
            'setExpectedColumns->setUnexpectedColumns->getData'
        )->andThrow($mockError);

        $this->logger->shouldReceive('error');

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            'lay_csv_processing - failure - Error processing CSV: Invalid file. Cannot find expected header',
            $output
        );
    }
}
