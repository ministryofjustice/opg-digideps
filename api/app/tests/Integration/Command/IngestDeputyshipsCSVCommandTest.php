<?php

namespace App\Tests\Integration\Entity\Command;

use App\Command\IngestDeputyshipsCSVCommand;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVIngester;
use App\v2\Registration\DeputyshipProcessing\DeputyshipsCSVIngestResult;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class IngestDeputyshipsCSVCommandTest extends KernelTestCase
{
    private string $csvFilename;
    private S3Client|MockObject $s3;
    private ParameterBag $params;
    private DeputyshipsCSVIngester|MockObject $deputyshipsCSVIngester;
    private LoggerInterface $logger;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->csvFilename = '(DigiDeps)_Deputyships_Report.csv';
        copy(dirname(dirname(__DIR__)).'/csv/'.$this->csvFilename, '/tmp/'.$this->csvFilename);

        $this->s3 = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->addMethods(['getObject'])
            ->getMock();
        $this->params = new ParameterBag(['s3_sirius_bucket' => 'bucket']);
        $this->deputyshipsCSVIngester = $this->createMock(DeputyshipsCSVIngester::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $setUp = new IngestDeputyshipsCSVCommand(
            $this->s3,
            $this->params,
            $this->deputyshipsCSVIngester,
            $this->logger
        );

        $app->add($setUp);

        $command = $app->find(IngestDeputyshipsCSVCommand::$defaultName);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithFailedFilePullS3Error(): void
    {
        $mockException = $this->createMock(S3Exception::class);
        $mockException->expects($this->once())
            ->method('getAwsErrorCode')
            ->willReturn('oops');

        $this->s3->expects($this->once())
            ->method('getObject')
            ->willThrowException($mockException);

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            'failure - Error retrieving file (DigiDeps)_Deputyships_Report.csv from bucket',
            $output
        );
    }

    public function testExecuteWithFailedCSVProcessing(): void
    {
        $this->s3->expects($this->once())
            ->method('getObject')
            ->willReturn(new Result());

        $this->deputyshipsCSVIngester->expects($this->once())
            ->method('processCsv')
            ->with('/tmp/'.$this->csvFilename)
            ->willReturn(new DeputyshipsCSVIngestResult(false, 'unable to parse file'));

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            'failure - Output: unable to parse file',
            $output
        );
    }

    public function testExecuteWithSuccessfulFilePull(): void
    {
        $this->s3->expects($this->once())
            ->method('getObject')
            ->willReturn(new Result());

        $this->deputyshipsCSVIngester->expects($this->once())
            ->method('processCsv')
            ->with('/tmp/'.$this->csvFilename)
            ->willReturn(new DeputyshipsCSVIngestResult(true, "CSV {$this->csvFilename} processed"));

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $this->commandTester->assertCommandIsSuccessful();
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            "success - Finished processing CourtOrders CSV, Output: CSV {$this->csvFilename} processed",
            $output
        );
    }
}
