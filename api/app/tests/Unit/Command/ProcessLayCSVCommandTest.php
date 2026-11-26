<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Command;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use OPG\Digideps\Backend\Command\ProcessLayCSVCommand;
use OPG\Digideps\Backend\Repository\PreRegistrationRepository;
use OPG\Digideps\Backend\Service\DeputyCaseService;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Tests\OPG\Digideps\Backend\Unit\S3ClientMock;

final class ProcessLayCSVCommandTest extends KernelTestCase
{
    private const CSV_FILENAME = 'layDeputyReport.csv';

    public function testExecuteWithSuccessfulFilePull(): void
    {
        copy(dirname(dirname(__DIR__)) . '/csv/layDeputyReport.csv', '/tmp/layDeputyReport.csv');

        $params = self::createMock(ParameterBagInterface::class);
        $params->expects(self::once())
            ->method('get')
            ->with('s3_sirius_bucket')
            ->willReturn('bucket');

        $s3 = self::createMock(S3ClientMock::class);
        $s3->expects(self::once())
            ->method('getObject')
            ->willReturn(new Result());

        $csvProcessing = self::createMock(CSVDeputyshipProcessing::class);
        $csvProcessing->expects(self::once())
            ->method('layProcessing')
            ->willReturn([
                'added' => 1,
                'errors' => 0,
                'report-update-count' => 0,
                'cases-with-updated-reports' => 0,
                'source' => 'sirius',
            ]);

        $csvProcessing->expects(self::once())
            ->method('layProcessingHandleNewMultiClients')
            ->willReturn([
                'new-clients-found' => 0,
                'clients-added' => 0,
                'errors' => [],
                'details' => [],
            ]);

        $deputyCaseService = self::createMock(DeputyCaseService::class);
        $deputyCaseService->expects(self::once())
            ->method('addMissingDeputyCaseAssociations')
            ->willReturn(0);

        $commandTester = $this->commandTesterFor(new ProcessLayCSVCommand(
            $s3,
            $params,
            self::createStub(LoggerInterface::class),
            $csvProcessing,
            self::createStub(PreRegistrationRepository::class),
            $deputyCaseService,
        ));
        $commandTester->execute(['csv-filename' => self::CSV_FILENAME]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString(
            'lay_csv_processing - success - Finished processing LayCSV. Output:',
            $output
        );
    }

    public function testExecuteWithFailedFilePullS3Error(): void
    {
        $params = self::createMock(ParameterBagInterface::class);
        $params->expects(self::once())
            ->method('get')
            ->with('s3_sirius_bucket')
            ->willReturn('bucket');

        $s3 = self::createMock(S3ClientMock::class);
        $s3->expects(self::once())
            ->method('getObject')
            ->willThrowException(self::createStub(S3Exception::class));

        $commandTester = $this->commandTesterFor(new ProcessLayCSVCommand(
            $s3,
            $params,
            self::createStub(LoggerInterface::class),
            self::createStub(CSVDeputyshipProcessing::class),
            self::createStub(PreRegistrationRepository::class),
            self::createStub(DeputyCaseService::class),
        ));
        $commandTester->execute(['csv-filename' => self::CSV_FILENAME]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString(
            'lay_csv_processing - failure - Error retrieving file layDeputyReport.csv from bucket',
            $output
        );
    }

    public function testExecuteWithMissingCSVCol(): void
    {
        // Required so we can trigger missing column exception with bad file
        copy(dirname(dirname(__DIR__)) . '/csv/layDeputyReport-bad.csv', '/tmp/layDeputyReport.csv');

        $params = self::createMock(ParameterBagInterface::class);
        $params->expects(self::once())
            ->method('get')
            ->with('s3_sirius_bucket')
            ->willReturn('bucket');

        $s3 = self::createMock(S3ClientMock::class);
        $s3->expects(self::once())
            ->method('getObject')
            ->willReturn(new Result());

        $commandTester = $this->commandTesterFor(new ProcessLayCSVCommand(
            $s3,
            $params,
            self::createStub(LoggerInterface::class),
            self::createStub(CSVDeputyshipProcessing::class),
            self::createStub(PreRegistrationRepository::class),
            self::createStub(DeputyCaseService::class),
        ));
        $commandTester->execute(['csv-filename' => self::CSV_FILENAME]);
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString(
            'CSV file /tmp/' . self::CSV_FILENAME . ' does not contain all expected columns in header',
            $output
        );
    }

    private function commandTesterFor(ProcessLayCSVCommand $command): CommandTester
    {
        $app = new Application(self::createKernel());
        $app->add($command);
        $commandName = ProcessLayCSVCommand::$defaultName;
        self::assertIsString($commandName);

        return new CommandTester($app->find($commandName));
    }
}
