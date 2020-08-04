<?php declare(strict_types=1);

namespace AppBundle\Service;


use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Model\Sirius\QueuedDocumentData;
use AppBundle\Model\Sirius\SiriusApiError;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use AppBundle\Service\File\Storage\S3Storage;
use Aws\Command;
use Aws\S3\Exception\S3Exception;
use DateTime;
use DigidepsTests\Helpers\DocumentHelpers;
use DigidepsTests\Helpers\SiriusHelpers;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\Serializer;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DocumentSyncServiceTest extends KernelTestCase
{
    /** @var S3Storage&ObjectProphecy $s3Storage */
    private $s3Storage;

    /** @var SiriusApiGatewayClient&ObjectProphecy $siriusApiGatewayClient */
    private $siriusApiGatewayClient;

    /** @var RestClient|ObjectProphecy $restClient */
    private $restClient;

    /** @var SiriusApiErrorTranslator|ObjectProphecy $restClient */
    private $errorTranslator;

    /** @var Serializer $serializer */
    private $serializer;

    /** @var DateTime */
    private $reportSubmittedDate, $reportEndDate, $reportStartDate;

    /** @var int */
    private $reportSubmissionId;

    /** @var string */
    private $reportPdfSubmissionUuid, $fileName, $s3Reference;

    public function setUp(): void
    {
        /** @var S3Storage&ObjectProphecy $s3Storage */
        $this->s3Storage = self::prophesize(S3Storage::class);

        /** @var SiriusApiGatewayClient&ObjectProphecy $siriusApiGatewayClient */
        $this->siriusApiGatewayClient = self::prophesize(SiriusApiGatewayClient::class);

        /** @var RestClient|ObjectProphecy $restClient */
        $this->restClient = self::prophesize(RestClient::class);

        /** @var SiriusApiErrorTranslator|ObjectProphecy $restClient */
        $this->errorTranslator = self::prophesize(SiriusApiErrorTranslator::class);

        /** @var Serializer serializer */
        $this->serializer = (self::bootKernel(['debug' => false]))->getContainer()->get('jms_serializer');

        $this->reportStartDate = new DateTime('2018-05-14');
        $this->reportEndDate = new DateTime('2019-05-13');
        $this->reportSubmittedDate = new DateTime('2019-06-20');
        $this->reportSubmissionId = 9876;
        $this->reportPdfSubmissionUuid = '5a8b1a26-8296-4373-ae61-f8d0b250e123';
        $this->fileName = 'test.pdf';
        $this->s3Reference = 'dd_doc_98765_01234567890123';
    }

    /**
     * @test
     * @dataProvider reportTypeProvider
     */
    public function syncDocument_report_pdf_sync_success(string $reportTypeCode, string $expectedReportType)
    {
        $reportPdfReportSubmission =
            (new ReportSubmission())
                ->setId($this->reportSubmissionId)
                ->setUuid($this->reportPdfSubmissionUuid);

        $queuedDocumentData = (new QueuedDocumentData())
            ->setReportType($reportTypeCode)
            ->setDocumentId(6789)
            ->setReportSubmissionId($this->reportSubmissionId)
            ->setReportSubmissions([$reportPdfReportSubmission])
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setFilename('test.pdf')
            ->setIsReportPdf(true)
            ->setCaseNumber('1234567t')
            ->setNdrId(null)
            ->setStorageReference($this->s3Reference);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportSubmittedDate,
            $expectedReportType,
            $this->reportSubmissionId,
            $this->fileName,
           null,
            $this->s3Reference
        );

        $successResponseBody = ['data' => ['id' => $this->reportPdfSubmissionUuid]];
        $successResponse = new Response('200', [], json_encode($successResponseBody));

        $this->siriusApiGatewayClient->sendReportPdfDocument($siriusDocumentUpload, '1234567T')
            ->shouldBeCalled()
            ->willReturn($successResponse);

        $this->restClient
            ->apiCall('put',
                'report-submission/9876/update-uuid',
                json_encode(['uuid' => $this->reportPdfSubmissionUuid]),
                'raw',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn(new SymfonyResponse('9876'));

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(['syncStatus' => Document::SYNC_STATUS_SUCCESS]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn(new Document());

        $sut = new DocumentSyncService(
            $this->s3Storage->reveal(),
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal()
        );

        $sut->syncDocument($queuedDocumentData);
    }

    public function reportTypeProvider()
    {
        return [
            'Report type 102' => [Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS, 'PF'],
            'Report type 103' => [Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS, 'PF'],
            'Report type 102-4' => [Report::TYPE_COMBINED_HIGH_ASSETS, 'HW'],
            'Report type 103-4' => [Report::TYPE_COMBINED_LOW_ASSETS, 'HW'],
            'Report type 104' => [Report::TYPE_HEALTH_WELFARE, 'HW'],
        ];
    }

    /**
     * @test
     */
    public function syncDocument_report_pdf_ndr_sync_success()
    {
        $reportPdfReportSubmission =
            (new ReportSubmission())
                ->setId($this->reportSubmissionId)
                ->setUuid($this->reportPdfSubmissionUuid);

        $queuedDocumentData = (new QueuedDocumentData())
            ->setReportType(null)
            ->setDocumentId(6789)
            ->setReportSubmissionId($this->reportSubmissionId)
            ->setReportSubmissions([$reportPdfReportSubmission])
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setFilename('test.pdf')
            ->setIsReportPdf(true)
            ->setCaseNumber('1234567t')
            ->setNdrId(123)
            ->setStorageReference($this->s3Reference);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $this->reportStartDate,
            $this->reportStartDate,
            $this->reportSubmittedDate,
            'NDR',
            $this->reportSubmissionId,
            $this->fileName,
            null,
            $this->s3Reference
        );

        $successResponseBody = ['data' => ['id' => $this->reportPdfSubmissionUuid]];
        $successResponse = new Response('200', [], json_encode($successResponseBody));

        $this->siriusApiGatewayClient->sendReportPdfDocument($siriusDocumentUpload, '1234567T')
            ->shouldBeCalled()
            ->willReturn($successResponse);

        $this->restClient
            ->apiCall('put',
                'report-submission/9876/update-uuid',
                json_encode(['uuid' => $this->reportPdfSubmissionUuid]),
                'raw',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn(new SymfonyResponse('9876'));

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(['syncStatus' => Document::SYNC_STATUS_SUCCESS]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn(new Document());

        $sut = new DocumentSyncService(
            $this->s3Storage->reveal(),
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal()
        );

        $sut->syncDocument($queuedDocumentData);
    }

    /** @test */
    public function sendDocument_sync_failure_sirius_report_pdf()
    {
        $reportPdfReportSubmission =
            (new ReportSubmission())
                ->setId($this->reportSubmissionId)
                ->setUuid($this->reportPdfSubmissionUuid);

        $queuedDocumentData = (new QueuedDocumentData())
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId(6789)
            ->setReportSubmissionId($this->reportSubmissionId)
            ->setReportSubmissions([$reportPdfReportSubmission])
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setFilename('test.pdf')
            ->setIsReportPdf(true)
            ->setCaseNumber('1234567t')
            ->setNdrId(null)
            ->setStorageReference($this->s3Reference);


        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportSubmittedDate,
            'PF',
            $this->reportSubmissionId,
            $this->fileName,
            null,
            $this->s3Reference
            );

        $failureResponseBody = ['errors' => [0 => ['id' => 'ABC123', 'code' => 'OPGDATA-API-FORBIDDEN']]];
        $failureResponse = new Response('403', [], json_encode($failureResponseBody));

        $requestException = new RequestException('An error occurred', new Request('POST', '/report-submission/9876/update-uuid'), $failureResponse);

        $this->siriusApiGatewayClient->sendReportPdfDocument($siriusDocumentUpload, '1234567T')
            ->shouldBeCalled()
            ->willThrow($requestException);

        $this->errorTranslator->translateApiError(json_encode($failureResponseBody))->willReturn(
            'OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions'
        );

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(
                    ['syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR,
                    'syncError' => 'OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions'
                    ]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService(
            $this->s3Storage->reveal(),
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal()
        );

        $sut->syncDocument($queuedDocumentData);

        self::assertContains($queuedDocumentData->getReportSubmissionId(), $sut->getSyncErrorSubmissionIds());
    }

    /**
     * @test
     */
    public function sendSupportingDocument_success()
    {
        $document = (new Document())->setId(6789);

        $expectedUuidUsedToSyncDoc = 'report-pdf-submission-uuid';
        $expectedSubmissionIdUsedForSync = 1234;
        $expectedCaseRefUsedForSync = '1234567T';

        $queuedDocumentData = (new QueuedDocumentData())
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId($document->getId())
            ->setReportSubmissionId($expectedSubmissionIdUsedForSync)
            ->setReportSubmissionUuid($expectedUuidUsedToSyncDoc)
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setFilename('bank-statement.pdf')
            ->setIsReportPdf(false)
            ->setCaseNumber('1234567t')
            ->setNdrId(null)
            ->setStorageReference($this->s3Reference);

        $successResponseBody = ['data' => ['type' => 'supportingDocument', 'id' => 'a-random-uuid']];
        $successResponse = new Response('200', [], json_encode($successResponseBody));

        $siriusDocumentUpload = SiriusHelpers::generateSiriusSupportingDocumentUpload(
            $expectedSubmissionIdUsedForSync,
            'bank-statement.pdf',
            null,
            $this->s3Reference
        );

        $this->siriusApiGatewayClient
            ->sendSupportingDocument($siriusDocumentUpload, $expectedUuidUsedToSyncDoc, $expectedCaseRefUsedForSync)
            ->shouldBeCalled()
            ->willReturn($successResponse);

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(['syncStatus' => Document::SYNC_STATUS_SUCCESS]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn(new Document());

        $sut = new DocumentSyncService(
            $this->s3Storage->reveal(),
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal()
        );

        $sut->syncDocument($queuedDocumentData);
    }

    /** @test */
    public function sendSupportingDocument_report_pdf_not_submitted()
    {
        $queuedDocumentData = (new QueuedDocumentData())
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId(6789)
            ->setReportSubmissionId($this->reportSubmissionId)
            ->setReportSubmissionUuid(null)
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setFilename('bank-statement.pdf')
            ->setIsReportPdf(false)
            ->setCaseNumber('1234567t')
            ->setNdrId(null)
            ->setStorageReference($this->s3Reference);

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(['syncStatus' => Document::SYNC_STATUS_QUEUED]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService(
            $this->s3Storage->reveal(),
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal()
        );

        $sut->syncDocument($queuedDocumentData);
    }

    /** @test */
    public function sendSupportingDocument_sync_failure()
    {
        $queuedDocumentData = (new QueuedDocumentData())
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId(6789)
            ->setReportSubmissionId($this->reportSubmissionId)
            ->setReportSubmissionUuid('report-pdf-uuid')
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setFilename('bank-statement.pdf')
            ->setIsReportPdf(false)
            ->setCaseNumber('1234567t')
            ->setNdrId(null)
            ->setStorageReference($this->s3Reference);;

        $failureResponseBody = ['errors' => [0 => ['id' => 'ABC123', 'code' => 'OPGDATA-API-FORBIDDEN']]];
        $failureResponse = new Response('403', [], json_encode($failureResponseBody));

        $requestException = new RequestException('An error occurred', new Request('POST', '/report-submission/9876/update-uuid'), $failureResponse);

        $this->siriusApiGatewayClient->sendSupportingDocument(Argument::cetera())
            ->shouldBeCalled()
            ->willThrow($requestException);

        $this->errorTranslator->translateApiError(json_encode($failureResponseBody))->willReturn(
            'OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions'
        );

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(
                    ['syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR,
                        'syncError' => 'OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions'
                    ]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService(
            $this->s3Storage->reveal(),
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal()
        );

        $sut->syncDocument($queuedDocumentData);

        self::assertNotContains($queuedDocumentData->getReportSubmissionId(), $sut->getSyncErrorSubmissionIds());
    }
}
