<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Command;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use OPG\Digideps\Backend\Command\ProcessLayCSVCommand;
use OPG\Digideps\Backend\Repository\PreRegistrationRepository;
use OPG\Digideps\Backend\Service\DeputyCaseService;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Tests\OPG\Digideps\Backend\Unit\S3ClientMock;

final class ProcessLayCSVCommandTest extends KernelTestCase
{
    private S3ClientMock&MockObject $s3;
    private ParameterBagInterface&MockObject $params;
    private string $csvFilename;
    private LoggerInterface&MockObject $logger;
    private CSVDeputyshipProcessing&MockObject $csvProcessing;
    private PreRegistrationRepository&MockObject $preReg;
    private DeputyCaseService&MockObject $deputyCaseService;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $kernel = self::createKernel();
        $app = new Application($kernel);

        // TODO Refactor CSV Process so we can mock this properly
        copy(dirname(dirname(__DIR__)) . '/csv/layDeputyReport.csv', '/tmp/layDeputyReport.csv');

        $this->s3 = self::createMock(S3ClientMock::class);

        $this->params = self::createMock(ParameterBagInterface::class);
        $this->params->expects(self::once())
            ->method('get')
            ->with('s3_sirius_bucket')
            ->willReturn('bucket');

        $this->csvFilename = 'layDeputyReport.csv';

        $this->logger = self::createMock(LoggerInterface::class);
        $this->csvProcessing = self::createMock(CSVDeputyshipProcessing::class);
        $this->preReg = self::createMock(PreRegistrationRepository::class);
        $this->deputyCaseService = self::createMock(DeputyCaseService::class);

        $setUp = new ProcessLayCSVCommand(
            $this->s3,
            $this->params,
            $this->logger,
            $this->csvProcessing,
            $this->preReg,
            $this->deputyCaseService,
        );

        $app->add($setUp);

        $command = $app->find(ProcessLayCSVCommand::$defaultName);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithSuccessfulFilePull(): void
    {
        $this->s3->expects(self::once())
            ->method('getObject')
            ->willReturn(new Result());

        $this->csvProcessing->expects(self::once())
            ->method('layProcessing')
            ->willReturn([
                'added' => 1,
                'errors' => 0,
                'report-update-count' => 0,
                'cases-with-updated-reports' => 0,
                'source' => 'sirius',
            ]);

        $this->csvProcessing->expects(self::once())
            ->method('layProcessingHandleNewMultiClients')
            ->willReturn([
                'new-clients-found' => 0,
                'clients-added' => 0,
                'errors' => [],
                'details' => [],
            ]);

        $this->deputyCaseService->expects(self::once())
            ->method('addMissingDeputyCaseAssociations')
            ->willReturn(0);

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
        $this->s3->expects(self::once())
            ->method('getObject')
            ->willThrowException(self::createStub(S3Exception::class));

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            'lay_csv_processing - failure - Error retrieving file layDeputyReport.csv from bucket',
            $output
        );
    }

    public function testExecuteWithMissingCSVCol(): void
    {
        // Required so we can trigger missing column exception with bad file
        copy(dirname(dirname(__DIR__)) . '/csv/layDeputyReport-bad.csv', '/tmp/layDeputyReport.csv');

        $this->s3->expects(self::once())
            ->method('getObject')
            ->willReturn(new Result());

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            "CSV file /tmp/{$this->csvFilename} does not contain all expected columns in header",
            $output
        );
    }
}
