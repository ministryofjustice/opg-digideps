<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Sync\Service;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\SerializerInterface;
use OPG\Digideps\Frontend\Entity\Report\Document;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\Report\ReportSubmission;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Sync\Model\Sirius\QueuedDocumentData;
use OPG\Digideps\Frontend\Sync\Service\Client\Sirius\SiriusApiGatewayClient;
use OPG\Digideps\Frontend\Sync\Service\DocumentSyncService;
use OPG\Digideps\Frontend\Sync\Service\SiriusApiErrorTranslator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Tests\OPG\Digideps\Frontend\Unit\Helpers\SiriusHelpers;

class DocumentSyncServiceTest extends KernelTestCase
{
    private SiriusApiGatewayClient&MockObject $siriusApiGatewayClient;
    private RestClient&MockObject $restClient;
    private SiriusApiErrorTranslator&MockObject $errorTranslator;
    private SerializerInterface $serializer;
    private \DateTime $reportSubmittedDate;
    private \DateTime $reportEndDate;
    private \DateTime $reportStartDate;
    private int $reportSubmissionId;
    private string $reportPdfSubmissionUuid;
    private string $fileName;
    private string $s3Reference;

    public function setUp(): void
    {
        $this->reportStartDate = new \DateTime('2018-05-14');
        $this->reportEndDate = new \DateTime('2019-05-13');
        $this->reportSubmittedDate = new \DateTime('2019-06-20');
        $this->reportSubmissionId = 9876;
        $this->reportPdfSubmissionUuid = '5a8b1a26-8296-4373-ae61-f8d0b250e123';
        $this->fileName = 'test.pdf';
        $this->s3Reference = 'dd_doc_98765_01234567890123';

        /* @var SiriusApiGatewayClient&MockObject $siriusApiGatewayClient */
        $this->siriusApiGatewayClient = self::createMock(SiriusApiGatewayClient::class);

        /* @var RestClient&MockObject $restClient */
        $this->restClient = self::createMock(RestClient::class);

        /* @var SiriusApiErrorTranslator&MockObject $errorTranslator */
        $this->errorTranslator = self::createMock(SiriusApiErrorTranslator::class);

        /* @var SerializerInterface $serializer */
        $serializer = (self::bootKernel(['debug' => false]))->getContainer()->get('jms_serializer');
        $this->serializer = $serializer;
    }

    /**
     * @dataProvider reportTypeProvider
     */
    public function testSyncDocumentReportPdfSyncSuccess(string $reportTypeCode, string $expectedReportType): void
    {
        $reportPdfReportSubmission =
            new ReportSubmission()
                ->setId($this->reportSubmissionId)
                ->setUuid($this->reportPdfSubmissionUuid);

        $queuedDocumentData = new QueuedDocumentData()
            ->setReportType($reportTypeCode)
            ->setDocumentId(6789)
            ->setReportSubmissionId($this->reportSubmissionId)
            ->setReportSubmissions([$reportPdfReportSubmission])
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setFilename($this->fileName)
            ->setIsReportPdf(true)
            ->setCaseNumber('1234567t')
            ->setStorageReference($this->s3Reference);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportSubmittedDate,
            $expectedReportType,
            $this->reportSubmissionId,
            $this->fileName,
            null,
            $this->s3Reference,
            $reportTypeCode
        );

        $successResponseBody = ['data' => ['id' => $this->reportPdfSubmissionUuid]];
        $successResponse = new Response(200, [], json_encode($successResponseBody));

        $this->siriusApiGatewayClient->expects(self::once())
            ->method('sendReportPdfDocument')
            ->with($siriusDocumentUpload, '1234567T')
            ->willReturn($successResponse);

        $this->setRestClientExpectations([
            [
                'args' => [
                    'put',
                    'report-submission/9876/update-uuid',
                    json_encode(['uuid' => $this->reportPdfSubmissionUuid]),
                    'raw',
                    [],
                    false,
                ],
                'return' => new SymfonyResponse('6789')
            ],
            [
                'args' => [
                    'put',
                    'document/6789',
                    json_encode(['syncStatus' => Document::SYNC_STATUS_SUCCESS]),
                    Document::class,
                    [],
                    false
                ],
                'return' => new Document(),
            ]
        ]);

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient,
            $this->restClient,
            $this->errorTranslator,
        );

        $sut->syncDocument($queuedDocumentData);
    }

    public function reportTypeProvider(): array
    {
        return [
            'Report type 102' => [Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS, 'PF'],
            'Report type 103' => [Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS, 'PF'],
            'Report type 102-4' => [Report::TYPE_COMBINED_HIGH_ASSETS, 'HW'],
            'Report type 103-4' => [Report::TYPE_COMBINED_LOW_ASSETS, 'HW'],
            'Report type 104' => [Report::TYPE_HEALTH_WELFARE, 'HW'],
        ];
    }

    public function testSendDocumentSyncFailureSiriusReportPdf(): void
    {
        $reportPdfReportSubmission =
            new ReportSubmission()
                ->setId($this->reportSubmissionId)
                ->setUuid($this->reportPdfSubmissionUuid);

        $queuedDocumentData = new QueuedDocumentData()
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId(6789)
            ->setReportSubmissionId($this->reportSubmissionId)
            ->setReportSubmissions([$reportPdfReportSubmission])
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setFilename($this->fileName)
            ->setIsReportPdf(true)
            ->setCaseNumber('1234567t')
            ->setStorageReference($this->s3Reference);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportSubmittedDate,
            'PF',
            $this->reportSubmissionId,
            $this->fileName,
            null,
            $this->s3Reference,
            Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS
        );

        $failureResponseBody = ['errors' => [0 => ['id' => 'ABC123', 'code' => 'OPGDATA-API-FORBIDDEN']]];
        $failureResponse = new Response(403, [], json_encode($failureResponseBody));

        $requestException = new RequestException('An error occurred', new Request('POST', '/report-submission/9876/update-uuid'), $failureResponse);

        $this->siriusApiGatewayClient
            ->expects(self::once())
            ->method('sendReportPdfDocument')
            ->with($siriusDocumentUpload, '1234567T')
            ->willThrowException($requestException);

        $this->errorTranslator
            ->expects(self::once())
            ->method('translateApiError')
            ->with(json_encode($failureResponseBody))
            ->willReturn('OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions');

        $this->restClient
            ->expects(self::once())
            ->method('apiCall')
            ->with(
                'put',
                'document/6789',
                json_encode(
                    ['syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR,
                        'syncError' => 'OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions',
                    ]
                ),
                Document::class,
                [],
                false
            )
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient,
            $this->restClient,
            $this->errorTranslator,
        );

        $sut->syncDocument($queuedDocumentData);

        self::assertContains($queuedDocumentData->getReportSubmissionId(), $sut->getSyncErrorSubmissionIds());
    }

    public function testSendSupportingDocumentSuccess(): void
    {
        $document = new Document()->setId(6789);

        $expectedUuidUsedToSyncDoc = 'report-pdf-submission-uuid';
        $expectedSubmissionIdUsedForSync = 1234;
        $expectedCaseRefUsedForSync = '1234567T';

        $queuedDocumentData = new QueuedDocumentData()
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId($document->getId())
            ->setReportSubmissionId($expectedSubmissionIdUsedForSync)
            ->setReportSubmissionUuid($expectedUuidUsedToSyncDoc)
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setFilename($this->fileName)
            ->setIsReportPdf(false)
            ->setCaseNumber('1234567t')
            ->setStorageReference($this->s3Reference);

        $successResponseBody = ['data' => ['type' => 'supportingDocument', 'id' => 'a-random-uuid']];
        $successResponse = new Response(200, [], json_encode($successResponseBody));

        $siriusDocumentUpload = SiriusHelpers::generateSiriusSupportingDocumentUpload(
            $expectedSubmissionIdUsedForSync,
            $this->fileName,
            null,
            $this->s3Reference,
            Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS
        );

        $this->siriusApiGatewayClient
            ->expects(self::once())
            ->method('sendSupportingDocument')
            ->with($siriusDocumentUpload, $expectedUuidUsedToSyncDoc, $expectedCaseRefUsedForSync)
            ->willReturn($successResponse);

        $this->restClient
            ->expects(self::once())
            ->method('apiCall')
            ->with(
                'put',
                'document/6789',
                json_encode(['syncStatus' => Document::SYNC_STATUS_SUCCESS]),
                Document::class,
                [],
                false
            )
            ->willReturn(new Document());

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient,
            $this->restClient,
            $this->errorTranslator,
        );

        $sut->syncDocument($queuedDocumentData);
    }

    public function testSendSupportingDocumentReportPdfNotSubmitted(): void
    {
        $queuedDocumentData = new QueuedDocumentData()
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId(6789)
            ->setReportSubmissionId($this->reportSubmissionId)
            ->setReportSubmissionUuid(null)
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setFilename($this->fileName)
            ->setIsReportPdf(false)
            ->setCaseNumber('1234567t')
            ->setStorageReference($this->s3Reference);

        $this->restClient
            ->expects(self::once())
            ->method('apiCall')
            ->with(
                'put',
                'document/6789',
                json_encode(['syncStatus' => Document::SYNC_STATUS_QUEUED]),
                Document::class,
                [],
                false
            )
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient,
            $this->restClient,
            $this->errorTranslator,
        );

        $sut->syncDocument($queuedDocumentData);
    }

    public function testSendSupportingDocumentSyncFailure(): void
    {
        $queuedDocumentData = new QueuedDocumentData()
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId(6789)
            ->setReportSubmissionId($this->reportSubmissionId)
            ->setReportSubmissionUuid('report-pdf-uuid')
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setFilename($this->fileName)
            ->setIsReportPdf(false)
            ->setCaseNumber('1234567t')
            ->setStorageReference($this->s3Reference);

        $failureResponseBody = ['errors' => [0 => ['id' => 'ABC123', 'code' => 'OPGDATA-API-FORBIDDEN']]];
        $failureResponse = new Response(403, [], json_encode($failureResponseBody));

        $requestException = new RequestException('An error occurred', new Request('POST', '/report-submission/9876/update-uuid'), $failureResponse);

        $this->siriusApiGatewayClient
            ->expects(self::once())
            ->method('sendSupportingDocument')
            ->willThrowException($requestException);

        $this->errorTranslator
            ->expects(self::once())
            ->method('translateApiError')
            ->with(json_encode($failureResponseBody))
            ->willReturn('OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions');

        $this->restClient
            ->expects(self::once())
            ->method('apiCall')
            ->with(
                'put',
                'document/6789',
                json_encode(
                    ['syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR,
                        'syncError' => 'OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions',
                    ]
                ),
                Document::class,
                [],
                false
            )
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient,
            $this->restClient,
            $this->errorTranslator,
        );

        $sut->syncDocument($queuedDocumentData);

        self::assertNotContains($queuedDocumentData->getReportSubmissionId(), $sut->getSyncErrorSubmissionIds());
    }

    /**
     * @dataProvider errorCodeProvider
     */
    public function testSendDocumentSyncFailureSiriusErrorTypeBasedOnResponseCode(int $errorCode, string $expectedErrorType, int $syncAttempts): void
    {
        $reportPdfReportSubmission =
            new ReportSubmission()
                ->setId($this->reportSubmissionId)
                ->setUuid($this->reportPdfSubmissionUuid);

        $queuedDocumentData = new QueuedDocumentData()
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId(6789)
            ->setReportSubmissionId($this->reportSubmissionId)
            ->setReportSubmissions([$reportPdfReportSubmission])
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setStorageReference($this->s3Reference)
            ->setFilename($this->fileName)
            ->setIsReportPdf(true)
            ->setCaseNumber('1234567t')
            ->setDocumentSyncAttempts($syncAttempts);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportSubmittedDate,
            'PF',
            $this->reportSubmissionId,
            $this->fileName,
            null,
            $this->s3Reference,
            Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS
        );

        $failureResponseBody = ['errors' => [0 => ['id' => 'ABC123', 'code' => 'OPGDATA-API-FORBIDDEN']]];
        $failureResponse = new Response($errorCode, [], json_encode($failureResponseBody));

        $requestException = new RequestException('An error occurred', new Request('POST', '/report-submission/9876/update-uuid'), $failureResponse);

        $this->siriusApiGatewayClient
            ->expects(self::once())
            ->method('sendReportPdfDocument')
            ->with($siriusDocumentUpload, '1234567T')
            ->willThrowException($requestException);

        $this->errorTranslator
            ->expects(self::once())
            ->method('translateApiError')
            ->with(json_encode($failureResponseBody))
            ->willReturn('OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions');

        $this->restClient
            ->expects(self::once())
            ->method('apiCall')
            ->with(
                'put',
                'document/6789',
                json_encode(
                    ['syncStatus' => $expectedErrorType,
                        'syncError' => 'OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions',
                    ]
                ),
                Document::class,
                [],
                false
            )
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient,
            $this->restClient,
            $this->errorTranslator,
        );

        $sut->syncDocument($queuedDocumentData);
    }

    public function errorCodeProvider(): array
    {
        return [
            '4XX error code' => [400, Document::SYNC_STATUS_PERMANENT_ERROR, 0],
            '5XX error code' => [500, Document::SYNC_STATUS_TEMPORARY_ERROR, 0],
            '5XX error code - 4th attempt' => [500, Document::SYNC_STATUS_PERMANENT_ERROR, 3],
        ];
    }

    public function testSendDocumentInvalidFilenamesAreFixed(): void
    {
        $document = new Document()->setId(6789);

        $expectedUuidUsedToSyncDoc = 'report-pdf-submission-uuid';
        $expectedSubmissionIdUsedForSync = 1234;
        $expectedCaseRefUsedForSync = '1234567T';

        $queuedDocumentData = new QueuedDocumentData()
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId($document->getId())
            ->setReportSubmissionId($expectedSubmissionIdUsedForSync)
            ->setReportSubmissionUuid($expectedUuidUsedToSyncDoc)
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setFilename('test .pdf')
            ->setIsReportPdf(false)
            ->setCaseNumber('1234567t')
            ->setStorageReference($this->s3Reference);

        $successResponseBody = ['data' => ['type' => 'supportingDocument', 'id' => 'a-random-uuid']];
        $successResponse = new Response(200, [], json_encode($successResponseBody));

        $siriusDocumentUpload = SiriusHelpers::generateSiriusSupportingDocumentUpload(
            $expectedSubmissionIdUsedForSync,
            'test_.pdf',
            null,
            $this->s3Reference,
            Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS
        );

        $this->siriusApiGatewayClient
            ->expects(self::once())
            ->method('sendSupportingDocument')
            ->with($siriusDocumentUpload, $expectedUuidUsedToSyncDoc, $expectedCaseRefUsedForSync)
            ->willReturn($successResponse);

        $this->restClient
            ->expects(self::once())
            ->method('apiCall')
            ->with(
                'put',
                'document/6789',
                json_encode(['syncStatus' => Document::SYNC_STATUS_SUCCESS]),
                Document::class,
                [],
                false
            )
            ->willReturn(new Document());

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient,
            $this->restClient,
            $this->errorTranslator,
        );

        $sut->syncDocument($queuedDocumentData);
    }

    public function testSendDocumentMissingFileExtensionThrowsError(): void
    {
        $document = new Document()->setId(6789);

        $expectedUuidUsedToSyncDoc = 'report-pdf-submission-uuid';
        $expectedSubmissionIdUsedForSync = 1234;

        $queuedDocumentData = new QueuedDocumentData()
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId($document->getId())
            ->setReportSubmissionId($expectedSubmissionIdUsedForSync)
            ->setReportSubmissionUuid($expectedUuidUsedToSyncDoc)
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setFilename('filename-with-no-extension')
            ->setIsReportPdf(false)
            ->setCaseNumber('1234567t')
            ->setStorageReference($this->s3Reference);

        $this->siriusApiGatewayClient
            ->expects(self::never())
            ->method('sendSupportingDocument');

        $this->restClient
            ->expects(self::once())
            ->method('apiCall')
            ->with(
                'put',
                'document/6789',
                json_encode(
                    [
                        'syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR,
                        'syncError' => 'File extension is missing from filename. This file will need to be manually synced with Sirius',
                    ]
                ),
                Document::class,
                [],
                false
            )
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient,
            $this->restClient,
            $this->errorTranslator,
        );

        $sut->syncDocument($queuedDocumentData);
    }

    /**
     * $expectations is structured like this:
     * [['args' => [arg1, arg2, arg3], 'return' => returnValue], ...]
     */
    private function setRestClientExpectations(array $expectations): void
    {
        $matcher = self::exactly(count($expectations));

        $this->restClient
            ->expects($matcher)
            ->method('apiCall')
            ->willReturnCallback(function ($parameters) use ($matcher, $expectations) {
                $invocation = $matcher->getInvocationCount();

                if (!isset($expectations[$invocation])) {
                    throw new \LogicException('Unexpected number of invocations');
                }

                self::assertSame($expectations[$invocation]['args'], $parameters);

                return $expectations[$invocation]['return'];
            });
    }
}
