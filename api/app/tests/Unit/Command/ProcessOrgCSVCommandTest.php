<?php

namespace App\Tests\Unit\Entity\Command;

use App\Repository\PreRegistrationRepository;
use App\Service\DataImporter\CsvToArray;
use App\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3ClientInterface;
use Aws\Result;
use Mockery as Mock;
use Predis\ClientInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use App\Command\ProcessOrgCSVCommand;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProcessOrgCSVCommandTest extends KernelTestCase
{
    use ProphecyTrait;
    
    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->s3 = self::prophesize(S3ClientInterface::class);
        $this->params = self::prophesize(ParameterBagInterface::class);
        $this->params->get('s3_sirius_bucket')
            ->shouldBeCalled()
            ->willReturn('bucket');

        $this->params->get('pa_pro_report_csv_filename')
            ->shouldBeCalled()
            ->willReturn('paProDeputyReport.csv');
        
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->redis = self::prophesize(ClientInterface::class);
        $this->csvProcessing = self::prophesize(CSVDeputyshipProcessing::class);
        $this->preReg = self::prophesize(PreRegistrationRepository::class);
        
        $this->csvArray = Mock::mock(CsvToArray::class);

        $setUp = new ProcessOrgCSVCommand(
            $this->s3->reveal(),
            $this->params->reveal(),
            $this->logger->reveal(),
            $this->redis->reveal(),
            $this->csvProcessing->reveal(), 
            $this->preReg->reveal()
        );

        $app->add($setUp);

        $command = $app->find(ProcessOrgCSVCommand::$defaultName);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithSuccessfulFilePull(): void
    {
        $this->s3->getObject()
            ->shouldBeCalled()
            ->willReturn(new Result());

        $this->csvArray->shouldReceive(
            'setExpectedColumns->setUnexpectedColumns->getData'
        )->andReturn([
            '0' => [
                'Case' => '45454545',
                'ClientForename' => 'CHARLIE',
                'ClientSurname' => 'PUFF',
                'ClientDateOfBirth' => '1998-11-18',
                'ClientAddress1' => '32 ALSTON VERGE',
                'ClientAddress2' => 'BECKS POINT',
                'ClientAddress3' => 'LUTE',
                'ClientAddress4' => 'LONDONVILLE',
                'ClientAddress5' => '',
                'ClientPostcode' => 'SE1 ZYD',
                'DeputyUid' => '700000000001',
                'DeputyType' => 'PRO',
                'DeputyEmail' => 'professor@mccracken.com',
                'DeputyOrganisation' => 'McCracken Associates',
                'DeputyForename' => 'PROFESSOR',
                'DeputySurname' => 'MCCRACKEN',
                'DeputyAddress1' => 'MCCRACKEN INC',
                'DeputyAddress2' => '1 BIG ROAD',
                'DeputyAddress3' => 'OLD TOWN',
                'DeputyAddress4' => 'TOWNSVILLE',
                'DeputyAddress5' => '',
                'DeputyPostcode' => 'TW1 V99',
                'MadeDate' => '2018-08-09',
                'LastReportDay' => '2021-08-08',
                'ReportType' => 'OPG102',
                'OrderType' => 'pfa',
                'Hybrid' => 'SINGLE'
            ]
        ]);
        
        $this->commandTester->execute([]);
        $this->commandTester->assertCommandIsSuccessful();
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            'lay_csv_processing - success - Finished processing LayCSV. Output:', 
            $output
        );
    }

    public function testExecuteWithFailedFilePullS3Error(): void
    {
        $this->s3->getObject()
            ->shouldBeCalled()
            ->willThrow(S3Exception::class);

        $this->commandTester->execute([]);
        $this->commandTester->assertCommandIsSuccessful();
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
    'lay_csv_processing - failure - Error retrieving file layDeputyReport.csv from bucket bucket',
            $output
        );
    }
    
    

    public function testExecuteWithMissingCSVCol(): void
    {
        $mockError = new \RuntimeException('Invalid file. Cannot find expected header');
        
        $this->csvArray->shouldReceive(
            'setExpectedColumns->setUnexpectedColumns->getData'
        )->willThrow($mockError);

        $this->s3->getObject()
            ->shouldBeCalled()
            ->willReturn(new Result());

        $this->commandTester->execute([]);
        $this->commandTester->assertCommandIsSuccessful();
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            'lay_csv_processing - failure - Error processing CSV: Invalid file. Cannot find expected header',
            $output
        );
    }
}
