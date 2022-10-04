<?php

namespace App\Tests\Command;

use App\Controller\Synchronisation\SynchronisationController;
use App\Entity\Report\Report;
use App\Event\ChecklistsSynchronisedEvent;
use App\Event\DocumentsSynchronisedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Model\Sirius\QueuedDocumentData;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\DocumentSyncService;
use App\Service\ParameterStoreService;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class SynchronisationControllerTest extends TestCase
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
     * @var LoggerInterface
     */
    private $verboseLogger;
    /**
     * @var ReportApi
     */
    private $reportApi;
    /**
     * @var ObservableEventDispatcher
     */
    private $dispatcher;

    public function setUp(): void
    {
        $this->syncService = self::prophesize(DocumentSyncService::class);
        $this->restClient = self::prophesize(RestClient::class);
        $this->serializer = self::prophesize(SerializerInterface::class);
        $this->parameterStore = self::prophesize(ParameterStoreService::class);
        $this->verboseLogger = self::prophesize(LoggerInterface::class);
        $this->reportApi = self::prophesize(ReportApi::class);
        $this->dispatcher = self::prophesize(ObservableEventDispatcher::class);
    }

    /**
     * @test
     */
    public function synchroniseDocument(): void
    {
        $arrayQueuedDocumentData = [
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
        ];
        $rawQueuedDocumentData = json_encode($arrayQueuedDocumentData);

        $queuedDocumentData = (new QueuedDocumentData())
            ->setDocumentId(6789)
            ->setReportSubmissionId(1234)
            ->setNdrId(1234)
            ->setCaseNumber('1234abc')
            ->setIsReportPdf(true)
            ->setFilename('test.pdf')
            ->setStorageReference('stor-ref-123')
            ->setReportStartDate(new DateTime('2017-02-01', new DateTimeZone('Europe/London')))
            ->setReportEndDate(new DateTime('2018-01-31', new DateTimeZone('Europe/London')))
            ->setReportSubmitDate(new DateTime('2020-04-29 15:05:23', new DateTimeZone('Europe/London')))
            ->setReportType('104');

        $event = new DocumentsSynchronisedEvent([$queuedDocumentData]);

        $this->parameterStore
            ->getFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC)
            ->shouldBeCalled()
            ->willReturn('1');

        $this->parameterStore
            ->getParameter(ParameterStoreService::PARAMETER_DOCUMENT_SYNC_ROW_LIMIT)
            ->shouldBeCalled()
            ->willReturn('100');

        $this->restClient
            ->apiCall('get', 'document/queued-jwt', ['row_limit' => '100'], 'array', Argument::type('array'), false)
            ->shouldBeCalled()
            ->willReturn($rawQueuedDocumentData);

        $this->serializer
            ->deserialize($rawQueuedDocumentData, 'App\Model\Sirius\QueuedDocumentData[]', 'json')
            ->willReturn([$queuedDocumentData]);

        $this->verboseLogger->notice('Sync command completed')->shouldBeCalled();

        $this->verboseLogger->notice(sprintf('1 documents to upload'))->shouldBeCalled();

        $sut = new SynchronisationController(
            $this->restClient->reveal(),
            $this->serializer->reveal(),
            $this->parameterStore->reveal(),
            $this->verboseLogger->reveal(),
            $this->reportApi->reveal(),
            $this->dispatcher->reveal()
        );

        $this->dispatcher->dispatch($event, DocumentsSynchronisedEvent::NAME)->shouldBeCalled();

        $request = new Request();
        $sut->synchroniseDocument($request);
    }

    /**
     * @test
     */
    public function synchroniseDocumentTurnedOff()
    {
        $this->parameterStore
            ->getFeatureFlag(ParameterStoreService::FLAG_DOCUMENT_SYNC)
            ->shouldBeCalled()
            ->willReturn('0');

        $this->restClient
            ->apiCall(Argument::cetera())
            ->shouldNotBeCalled();

        $this->dispatcher
            ->dispatch(Argument::cetera())
            ->shouldNotBeCalled();

        $sut = new SynchronisationController(
            $this->restClient->reveal(),
            $this->serializer->reveal(),
            $this->parameterStore->reveal(),
            $this->verboseLogger->reveal(),
            $this->reportApi->reveal(),
            $this->dispatcher->reveal()
        );

        $request = new Request();
        $sut->synchroniseDocument($request);
    }

    /**
     * @test
     */
    public function synchroniseChecklist(): void
    {
        $queuedReportData = (new Report())
            ->setStartDate(new DateTime('2017-02-01', new DateTimeZone('Europe/London')))
            ->setEndDate(new DateTime('2018-01-31', new DateTimeZone('Europe/London')));

        $event = new ChecklistsSynchronisedEvent([$queuedReportData]);

        $this->parameterStore
            ->getFeatureFlag(ParameterStoreService::FLAG_CHECKLIST_SYNC)
            ->shouldBeCalled()
            ->willReturn('1');

        $this->parameterStore
            ->getParameter(ParameterStoreService::PARAMETER_CHECKLIST_SYNC_ROW_LIMIT)
            ->shouldBeCalled()
            ->willReturn('100');

        $this->reportApi
            ->getReportsWithQueuedChecklistsJwt(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn([$queuedReportData]);

        $this->verboseLogger->notice('Sync command completed')->shouldBeCalled();

        $this->verboseLogger->notice(sprintf('1 checklists to upload'))->shouldBeCalled();

        $this->dispatcher->dispatch($event, ChecklistsSynchronisedEvent::NAME)->shouldBeCalled();

        $sut = new SynchronisationController(
            $this->restClient->reveal(),
            $this->serializer->reveal(),
            $this->parameterStore->reveal(),
            $this->verboseLogger->reveal(),
            $this->reportApi->reveal(),
            $this->dispatcher->reveal()
        );

        $request = new Request();
        $sut->synchroniseChecklist($request);
    }

    /**
     * @test
     */
    public function synchroniseChecklistTurnedOff(): void
    {
        $this->parameterStore
            ->getFeatureFlag(ParameterStoreService::FLAG_CHECKLIST_SYNC)
            ->shouldBeCalled()
            ->willReturn('0');

        $this->parameterStore
            ->getParameter(Argument::cetera())
            ->shouldNotBeCalled();

        $this->reportApi
            ->getReportsWithQueuedChecklistsJwt(Argument::cetera())
            ->shouldNotBeCalled();

        $this->dispatcher->dispatch(Argument::cetera())->shouldNotBeCalled();

        $sut = new SynchronisationController(
            $this->restClient->reveal(),
            $this->serializer->reveal(),
            $this->parameterStore->reveal(),
            $this->verboseLogger->reveal(),
            $this->reportApi->reveal(),
            $this->dispatcher->reveal()
        );

        $request = new Request();
        $sut->synchroniseChecklist($request);
    }
}
