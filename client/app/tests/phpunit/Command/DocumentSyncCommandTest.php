<?php

namespace App\Tests\Command;

use App\Command\DocumentSyncCommand;
use App\Model\Sirius\QueuedDocumentData;
use App\Service\Client\RestClient;
use App\Service\DocumentSyncService;
use App\Service\ParameterStoreService;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DocumentSyncCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var ContainerInterface
     */
    protected static $container;
    /**
     * @var DocumentSyncService
     */
    private $syncService;
    /**
     * @var RestClient
     */
    private $restClient;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var ParameterStoreService
     */
    private $parameterStore;
    /**
     * @var CommandTester
     */
    private $commandTester;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->syncService = self::prophesize(DocumentSyncService::class);
        $this->restClient = self::prophesize(RestClient::class);
        $this->serializer = self::bootKernel()->getContainer()->get('serializer');
//        $this->serializer = self::bootKernel()->getContainer()->get(SerializerInterface::class);
        $this->parameterStore = self::prophesize(ParameterStoreService::class);

        $app->add(new DocumentSyncCommand($this->syncService->reveal(), $this->restClient->reveal(), $this->serializer, $this->parameterStore->reveal()));

        $command = $app->find(DocumentSyncCommand::$defaultName);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $rawQueuedDocumentData = json_encode([
            [
                'document_id' => 6789,
                'report_submission_id' => 1234,
                'ndr_id' => 1234,
                'case_number' => '1234abc',
                'is_report_pdf' => true,
                'filename' => 'test.pdf',
                'storage_reference' => 'stor-ref-123',
                'report_start_date' => '2017-02-01',
                'report_end_date' => '2018-01-31',
                'report_submit_date' => '2020-04-29 15:05:23',
                'report_type' => '104',
            ],
        ]);

        $queuedDocumentData = (new QueuedDocumentData())
            ->setDocumentId(6789)
            ->setReportSubmissionId(1234)
            ->setNdrId(1234)
            ->setCaseNumber('1234abc')
            ->setIsReportPdf(true)
            ->setFilename('test.pdf')
            ->setStorageReference('stor-ref-123')
            ->setReportStartDate(new \DateTime('2017-02-01', new \DateTimeZone('Europe/London')))
            ->setReportEndDate(new \DateTime('2018-01-31', new \DateTimeZone('Europe/London')))
            ->setReportSubmitDate(new \DateTime('2020-04-29 15:05:23', new \DateTimeZone('Europe/London')))
            ->setReportType('104');

        $this->parameterStore
            ->getFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC)
            ->shouldBeCalled()
            ->willReturn('1');

        $this->parameterStore
            ->getParameter(ParameterStoreService::PARAMETER_DOCUMENT_SYNC_ROW_LIMIT)
            ->shouldBeCalled()
            ->willReturn('100');

        $this->restClient
            ->apiCall('get', 'document/queued', ['row_limit' => '100'], 'array', Argument::type('array'), false)
            ->shouldBeCalled()
            ->willReturn($rawQueuedDocumentData);

        $this->syncService
            ->syncDocument($queuedDocumentData)
            ->shouldBeCalled();

        $this->syncService
            ->getDocsNotSyncedCount()
            ->shouldBeCalled()
            ->willReturn(0);

        $this->syncService
            ->getSyncErrorSubmissionIds()
            ->shouldBeCalled()
            ->willReturn([]);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('1 documents to upload', $output);
    }

    public function testSleepsWhenTurnedOff()
    {
        $this->parameterStore
            ->getFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC)
            ->shouldBeCalled()
            ->willReturn('0');

        $this->restClient
            ->apiCall(Argument::cetera())
            ->shouldNotBeCalled();

        $this->syncService
            ->syncDocument(Argument::cetera())
            ->shouldNotBeCalled();

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Feature disabled, sleeping', $output);
    }

    public function testExecuteWithSyncErrorSubmissionIds(): void
    {
        $this->parameterStore
            ->getFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC)
            ->shouldBeCalled()
            ->willReturn('1');

        $this->parameterStore
            ->getParameter(ParameterStoreService::PARAMETER_DOCUMENT_SYNC_ROW_LIMIT)
            ->shouldBeCalled()
            ->willReturn('100');

        $this->restClient
            ->apiCall('get', 'document/queued', ['row_limit' => '100'], 'array', Argument::type('array'), false)
            ->shouldBeCalled()
            ->willReturn(json_encode([]));

        /* @var DocumentSyncService|ObjectProphecy $documentSyncService */
        $this->syncService
            ->getSyncErrorSubmissionIds()
            ->shouldBeCalled()
            ->willReturn([1]);

        $this->syncService
            ->setSubmissionsDocumentsToPermanentError()
            ->shouldBeCalled();

        $this->syncService
            ->getDocsNotSyncedCount()
            ->shouldBeCalled()
            ->willReturn(6);

        $this->syncService
            ->setSyncErrorSubmissionIds([])
            ->shouldBeCalled();

        $this->syncService
            ->setDocsNotSyncedCount(0)
            ->shouldBeCalled();

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('0 documents to upload', $output);
        $this->assertStringContainsString('sync_documents_to_sirius - success - 6 documents remaining to sync', $output);
    }
}
