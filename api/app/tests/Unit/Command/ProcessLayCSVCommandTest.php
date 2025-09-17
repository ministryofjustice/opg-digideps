<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\ProcessLayCSVCommand;
use App\Repository\PreRegistrationRepository;
use App\Service\DataImporter\CsvToArray;
use App\Service\DeputyCaseService;
use App\Service\LayRegistrationService;
use App\Service\UserDeputyService;
use App\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Mockery as Mock;
use Mockery\MockInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class ProcessLayCSVCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    private ObjectProphecy|S3Client $s3;
    private ObjectProphecy|ParameterBagInterface $params;
    private string $csvFilename;
    private LoggerInterface|ObjectProphecy $logger;
    private ObjectProphecy|CSVDeputyshipProcessing $csvProcessing;
    private ObjectProphecy|PreRegistrationRepository $preReg;
    private ObjectProphecy|LayRegistrationService $layRegistrationService;
    private ObjectProphecy|DeputyCaseService $deputyCaseService;
    private ObjectProphecy|UserDeputyService $userDeputyService;
    private MockInterface&CsvToArray $csvArray;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        // TODO Refactor CSV Process so we can mock this properly
        copy(dirname(dirname(__DIR__)).'/csv/layDeputyReport.csv', '/tmp/layDeputyReport.csv');

        $this->s3 = self::prophesize(S3Client::class);
        $this->params = self::prophesize(ParameterBagInterface::class);
        $this->params->get('s3_sirius_bucket')
            ->shouldBeCalled()
            ->willReturn('bucket');

        $this->csvFilename = 'layDeputyReport.csv';

        $this->logger = self::prophesize(LoggerInterface::class);
        $this->csvProcessing = self::prophesize(CSVDeputyshipProcessing::class);
        $this->preReg = self::prophesize(PreRegistrationRepository::class);
        $this->layRegistrationService = self::prophesize(LayRegistrationService::class);
        $this->deputyCaseService = self::prophesize(DeputyCaseService::class);
        $this->userDeputyService = self::prophesize(UserDeputyService::class);

        $this->csvArray = Mock::mock(CsvToArray::class);

        $setUp = new ProcessLayCSVCommand(
            $this->s3->reveal(),
            $this->params->reveal(),
            $this->logger->reveal(),
            $this->csvProcessing->reveal(),
            $this->preReg->reveal(),
            $this->layRegistrationService->reveal(),
            $this->deputyCaseService->reveal(),
            $this->userDeputyService->reveal(),
        );

        $app->add($setUp);

        $command = $app->find(ProcessLayCSVCommand::$defaultName);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithSuccessfulFilePull(): void
    {
        $this->s3->getObject(Argument::any())
            ->shouldBeCalled()
            ->willReturn(new Result());

        $this->csvProcessing->layProcessing(Argument::any(), Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'added' => 1,
                'errors' => 0,
                'report-update-count' => 0,
                'cases-with-updated-reports' => 0,
                'source' => 'sirius',
            ]);

        $this->csvProcessing->layProcessingHandleNewMultiClients(Argument::any())
            ->shouldBeCalled()
            ->willReturn([
                'new-clients-found' => 0,
                'clients-added' => 0,
                'errors' => [],
                'details' => [],
            ]);

        $this->layRegistrationService->addMissingReports()
            ->shouldBeCalled()
            ->willReturn(0);

        $this->deputyCaseService->addMissingDeputyCaseAssociations()
            ->shouldBeCalled()
            ->willReturn(0);

        $this->userDeputyService->addMissingUserDeputies()
            ->shouldBeCalled()
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
        $this->s3->getObject(Argument::any())
            ->shouldBeCalled()
            ->willThrow(S3Exception::class);

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
        copy(dirname(dirname(__DIR__)).'/csv/layDeputyReport-bad.csv', '/tmp/layDeputyReport.csv');

        $this->s3->getObject(Argument::any())
            ->shouldBeCalled()
            ->willReturn(new Result());

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            "CSV file /tmp/{$this->csvFilename} does not contain all expected columns in header",
            $output
        );
    }
}
