<?php

namespace App\Tests\Unit\Entity\Command;

use App\Command\ProcessOrgCSVCommand;
use App\Repository\PreRegistrationRepository;
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

class ProcessOrgCSVCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        // TODO Refactor CSV Process so we can mock this properly
        copy(dirname(dirname(__DIR__)).'/csv/paProDeputyReport.csv', '/tmp/paProDeputyReport.csv');

        $this->s3 = self::prophesize(S3Client::class);
        $this->params = self::prophesize(ParameterBagInterface::class);
        $this->params->get('s3_sirius_bucket')
            ->shouldBeCalled()
            ->willReturn('bucket');

        $this->csvFilename = 'paProDeputyReport.csv';

        $this->logger = self::prophesize(LoggerInterface::class);
        $this->csvProcessing = self::prophesize(CSVDeputyshipProcessing::class);
        $this->preReg = self::prophesize(PreRegistrationRepository::class);

        $this->csvArray = Mock::mock(CsvToArray::class);

        $setUp = new ProcessOrgCSVCommand(
            $this->s3->reveal(),
            $this->params->reveal(),
            $this->logger->reveal(),
            $this->csvProcessing->reveal(),
            $this->preReg->reveal()
        );

        $app->add($setUp);

        $command = $app->find(ProcessOrgCSVCommand::$defaultName);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteWithSuccessfulFilePull(): void
    {
        $this->s3->getObject(Argument::any())
            ->shouldBeCalled()
            ->willReturn(new Result());

        $this->csvProcessing->orgProcessing(Argument::any())
            ->shouldBeCalled()
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
        $this->s3->getObject(Argument::any())
            ->shouldBeCalled()
            ->willThrow(S3Exception::class);

        $this->commandTester->execute(['csv-filename' => $this->csvFilename]);
        $output = $this->commandTester->getDisplay();

        $this->assertStringContainsString(
            'org_csv_processing - failure - Error retrieving file paProDeputyReport.csv from bucket',
            $output
        );
    }

    public function testExecuteWithMissingCSVCol(): void
    {
        // Required so we can trigger missing column exception with bad file
        copy(dirname(dirname(__DIR__)).'/csv/paProDeputyReport-bad.csv', '/tmp/paProDeputyReport.csv');
        $mockError = new \RuntimeException('Invalid file. Cannot find expected header');

        $this->csvArray->shouldReceive(
            'setExpectedColumns->setUnexpectedColumns->getData'
        )->andThrow($mockError);

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
