<?php

namespace App\Command;

use App\v2\Registration\DeputyshipProcessing\CourtOrdersCSVProcessor;
use App\v2\Registration\DeputyshipProcessing\CSVProcessingResult;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class ProcessCourtOrdersCSVCommandTest extends KernelTestCase
{
    private string $csvFilename;
    private S3Client|MockObject $s3;
    private ParameterBag $params;
    private CourtOrdersCSVProcessor|MockObject $courtOrdersCSVProcessor;
    private LoggerInterface $logger;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->csvFilename = 'courtOrders.csv';
        copy(dirname(dirname(__DIR__)).'/csv/'.$this->csvFilename, '/tmp/'.$this->csvFilename);

        $this->s3 = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->addMethods(['getObject'])
            ->getMock();
        $this->params = new ParameterBag(['s3_sirius_bucket' => 'bucket']);
        $this->courtOrdersCSVProcessor = $this->createMock(CourtOrdersCSVProcessor::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $setUp = new ProcessCourtOrdersCSVCommand(
            $this->s3,
            $this->params,
            $this->courtOrdersCSVProcessor,
            $this->logger
        );

        $app->add($setUp);

        $command = $app->find(ProcessCourtOrdersCSVCommand::$defaultName);
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
            'failure - Error retrieving file courtOrders.csv from bucket',
            $output
        );
    }

    public function testExecuteWithFailedWriteToLocalFile(): void
    {
        $this->s3->expects($this->once())
            ->method('getObject')
            ->willReturn(new Result());

        $this->commandTester->execute(['csv-filename' => 'nonexistent.csv']);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString(
            'failure - could not read CSV from file: /tmp/nonexistent.csv',
            $output
        );
    }

    public function testExecuteWithSuccessfulFilePull(): void
    {
        $this->s3->expects($this->once())
            ->method('getObject')
            ->willReturn(new Result());

        $this->courtOrdersCSVProcessor->expects($this->once())
            ->method('processCsv')
            ->with('/tmp/'.$this->csvFilename)
            ->willReturn(new CSVProcessingResult(true, 'success'));

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $this->commandTester->assertCommandIsSuccessful();
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            'success - Finished processing CourtOrderCSV, Output:',
            $output
        );
    }
}
