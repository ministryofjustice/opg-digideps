<?php

namespace App\Tests\Unit\Command;

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
use App\Command\ProcessLayCSVCommand;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProcessLayCSVCommandTest extends KernelTestCase
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

        $this->params->get('lay_report_csv_filename')
            ->shouldBeCalled()
            ->willReturn('layDeputyReport-bad.csv');
        
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->redis = self::prophesize(ClientInterface::class);
        $this->csvProcessing = self::prophesize(CSVDeputyshipProcessing::class);
        $this->preReg = self::prophesize(PreRegistrationRepository::class);
        
        $this->csvArray = Mock::mock(CsvToArray::class);

        $setUp = new ProcessLayCSVCommand(
            $this->s3->reveal(),
            $this->params->reveal(),
            $this->logger->reveal(),
            $this->redis->reveal(),
            $this->csvProcessing->reveal(), 
            $this->preReg->reveal()
        );

        $app->add($setUp);

        $command = $app->find(ProcessLayCSVCommand::$defaultName);
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
                'Case' => '98989898',
                'ClientSurname' => 'SMITHY',
                'DeputyUid' => '19371937',
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
