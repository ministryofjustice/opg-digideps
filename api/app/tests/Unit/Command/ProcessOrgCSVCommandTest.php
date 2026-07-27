<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Command;

use Aws\Result;
use Aws\S3\Exception\S3Exception;
use OPG\Digideps\Backend\Command\ProcessOrgCSVCommand;
use OPG\Digideps\Backend\v2\Registration\DeputyshipProcessing\CSVDeputyshipProcessing;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Tests\OPG\Digideps\Backend\Unit\S3ClientMock;

final class ProcessOrgCSVCommandTest extends KernelTestCase
{
    private S3ClientMock&MockObject $s3;
    private ParameterBagInterface&MockObject $params;
    private string $csvFilename;
    private LoggerInterface&MockObject $logger;
    private CSVDeputyshipProcessing&MockObject $csvProcessing;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $kernel = self::createKernel();
        $app = new Application($kernel);

        // TODO Refactor CSV Process so we can mock this properly
        copy(dirname(dirname(__DIR__)) . '/csv/paProDeputyReport.csv', '/tmp/paProDeputyReport.csv');

        $this->s3 = self::createMock(S3ClientMock::class);

        $this->params = self::createMock(ParameterBagInterface::class);
        $this->params->expects(self::once())
            ->method('get')
            ->with('s3_sirius_bucket')
            ->willReturn('bucket');

        $this->csvFilename = 'paProDeputyReport.csv';

        $this->logger = self::createMock(LoggerInterface::class);
        $this->csvProcessing = self::createMock(CSVDeputyshipProcessing::class);

        $setUp = new ProcessOrgCSVCommand(
            $this->s3,
            $this->params,
            $this->logger,
            $this->csvProcessing,
        );

        $app->add($setUp);

        $command = $app->find(ProcessOrgCSVCommand::$defaultName);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithSuccessfulFilePull(): void
    {
        $this->s3->expects(self::once())
            ->method('getObject')
            ->willReturn(new Result());

        $this->csvProcessing->expects(self::once())
            ->method('orgProcessing')
            ->willReturn([
                'errors' => [
                    'count' => 1,
                    'messages' => ['ERROR'],
                ],
                'added' => [
                    'deputies' => [1],
                    'organisations' => [1],
                    'clients' => [1],
                    'reports' => [1],
                ],
                'updated' => [
                    'deputies' => [0],
                    'organisations' => [0],
                    'clients' => [0],
                    'reports' => [0],
                ],
                'changeOrg' => [
                    'deputies' => [0],
                    'organisations' => [0],
                    'clients' => [0],
                    'reports' => [0],
                ],
                'skipped' => 1,
            ]);

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $this->commandTester->assertCommandIsSuccessful();
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            'org_csv_processing - success - Finished processing OrgCSV, Output: ',
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
            'org_csv_processing - failure - Error retrieving file paProDeputyReport.csv from bucket',
            $output
        );
    }
}
