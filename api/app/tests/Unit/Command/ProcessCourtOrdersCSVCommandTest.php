<?php

namespace App\Command;

use App\Service\DataImporter\CsvToArray;
use App\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Mockery as Mock;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProcessCourtOrdersCSVCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        copy(dirname(dirname(__DIR__)) .'/csv/courtOrders.csv', '/tmp/courtOrders.csv');

        $this->s3 = self::prophesize(S3Client::class);
        $this->params = self::prophesize(ParameterBagInterface::class);
        $this->params->get('s3_sirius_bucket')
            ->shouldBeCalled()
            ->willReturn('bucket');

        $this->csvFilename = 'courtOrders.csv';

        $this->logger = self::prophesize(LoggerInterface::class);
        $this->csvProcessing = self::prophesize(CSVDeputyshipProcessing::class);
        $this->csvArray = Mock::mock(CsvToArray::class);

        $setUp = new ProcessCourtOrdersCSVCommand(
            $this->s3->reveal(),
            $this->params->reveal(),
            $this->logger->reveal(),
            $this->csvProcessing->reveal()
        );

        $app->add($setUp);

        $command = $app->find(ProcessCourtOrdersCSVCommand::$defaultName);
        $this->commandTester = new CommandTester($command);
    }
    
    public function testExecuteWithSuccessfulFilePull(): void
    {
        $this->s3->getObject(Argument::any())
            ->shouldBeCalled()
            ->willReturn(new Result());

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $this->commandTester->assertCommandIsSuccessful();
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            'success - Finished processing CourtOrderCSV, Output:',
            $output
        );
    }

    public function testExecuteWithFailedFilePullS3Error(): void
    {
        $this->s3->getObject(Argument::any())
            ->shouldBeCalled()
            ->willThrow(S3Exception::class);

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            'failure - Error retrieving file courtOrders.csv from bucket',
            $output
        );
    }
}
