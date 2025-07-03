<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Model\Sirius\QueuedDocumentData;
use App\Service\Client\RestClient;
use App\Service\Client\Sirius\SiriusApiGatewayClient;
use App\Service\File\FileNameFixer;
use DigidepsTests\Helpers\SiriusHelpers;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JMS\Serializer\Serializer;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\StreamInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DocumentSyncServiceTest extends KernelTestCase
{
    use ProphecyTrait;

    /** @var SiriusApiGatewayClient&ObjectProphecy */
    private $siriusApiGatewayClient;

    /** @var RestClient|ObjectProphecy */
    private $restClient;

    /** @var SiriusApiErrorTranslator|ObjectProphecy */
    private $errorTranslator;

    /** @var Serializer */
    private $serializer;

    /** @var FileNameFixer&ObjectProphecy */
    private $fileNameFixer;

    /** @var \DateTime */
    private $reportSubmittedDate;
    private $reportEndDate;
    private $reportStartDate;

    /** @var int */
    private $reportSubmissionId;

    /** @var string */
    private $reportPdfSubmissionUuid;
    private $fileName;
    private $s3Reference;

    public function setUp(): void
    {
        $this->reportStartDate = new \DateTime('2018-05-14');
        $this->reportEndDate = new \DateTime('2019-05-13');
        $this->reportSubmittedDate = new \DateTime('2019-06-20');
        $this->reportSubmissionId = 9876;
        $this->reportPdfSubmissionUuid = '5a8b1a26-8296-4373-ae61-f8d0b250e123';
        $this->fileName = 'test.pdf';
        $this->s3Reference = 'dd_doc_98765_01234567890123';

        /* @var SiriusApiGatewayClient&ObjectProphecy $siriusApiGatewayClient */
        $this->siriusApiGatewayClient = self::prophesize(SiriusApiGatewayClient::class);

        /* @var RestClient|ObjectProphecy $restClient */
        $this->restClient = self::prophesize(RestClient::class);

        /* @var SiriusApiErrorTranslator|ObjectProphecy $errorTranslator */
        $this->errorTranslator = self::prophesize(SiriusApiErrorTranslator::class);

        /* @var SiriusApiErrorTranslator|ObjectProphecy $fileNameFixer */
        $this->fileNameFixer = self::prophesize(FileNameFixer::class);
        $this->fileNameFixer->removeWhiteSpaceBeforeFileExtension(Argument::any())->willReturn($this->fileName);

        /* @var Serializer serializer */
        $this->serializer = self::bootKernel(['debug' => false])->getContainer()->get('jms_serializer');
    }

    /**
     * @test
     *
     * @dataProvider reportTypeProvider
     */
    public function syncDocumentReportPdfSyncSuccess(string $reportTypeCode, string $expectedReportType)
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
            ->setFilename($this->fileName)
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
        $successResponse = new Response(200, [], json_encode($successResponseBody));

        $this->siriusApiGatewayClient->sendReportPdfDocument($siriusDocumentUpload, '1234567T')
            ->shouldBeCalled()
            ->willReturn($successResponse);

        $mockStream = $this->createMock(StreamInterface::class);
        $this->restClient
            ->apiCall(
                'put',
                'report-submission/9876/update-uuid',
                json_encode(['uuid' => $this->reportPdfSubmissionUuid]),
                'raw',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($mockStream);

        $this->restClient
            ->apiCall(
                'put',
                'document/6789',
                json_encode(['syncStatus' => Document::SYNC_STATUS_SUCCESS]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn(new Document());

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal(),
            $this->fileNameFixer->reveal()
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
    public function syncDocumentReportPdfNdrSyncSuccess()
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
            ->setFilename($this->fileName)
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
        $successResponse = new Response(200, [], json_encode($successResponseBody));

        $this->siriusApiGatewayClient->sendReportPdfDocument($siriusDocumentUpload, '1234567T')
            ->shouldBeCalled()
            ->willReturn($successResponse);

        $mockStream = $this->createMock(StreamInterface::class);
        $this->restClient
            ->apiCall(
                'put',
                'report-submission/9876/update-uuid',
                json_encode(['uuid' => $this->reportPdfSubmissionUuid]),
                'raw',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($mockStream);

        $this->restClient
            ->apiCall(
                'put',
                'document/6789',
                json_encode(['syncStatus' => Document::SYNC_STATUS_SUCCESS]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn(new Document());

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal(),
            $this->fileNameFixer->reveal()
        );

        $sut->syncDocument($queuedDocumentData);
    }

    /** @test */
    public function sendDocumentSyncFailureSiriusReportPdf()
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
            ->setFilename($this->fileName)
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
        $failureResponse = new Response(403, [], json_encode($failureResponseBody));

        $requestException = new RequestException('An error occurred', new Request('POST', '/report-submission/9876/update-uuid'), $failureResponse);

        $this->siriusApiGatewayClient->sendReportPdfDocument($siriusDocumentUpload, '1234567T')
            ->shouldBeCalled()
            ->willThrow($requestException);

        $this->errorTranslator->translateApiError(json_encode($failureResponseBody))->willReturn(
            'OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions'
        );

        $this->restClient
            ->apiCall(
                'put',
                'document/6789',
                json_encode(
                    ['syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR,
                        'syncError' => 'OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions',
                    ]
                ),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal(),
            $this->fileNameFixer->reveal()
        );

        $sut->syncDocument($queuedDocumentData);

        self::assertContains($queuedDocumentData->getReportSubmissionId(), $sut->getSyncErrorSubmissionIds());
    }

    /**
     * @test
     */
    public function sendSupportingDocumentSuccess()
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
            ->setFilename($this->fileName)
            ->setIsReportPdf(false)
            ->setCaseNumber('1234567t')
            ->setNdrId(null)
            ->setStorageReference($this->s3Reference);

        $successResponseBody = ['data' => ['type' => 'supportingDocument', 'id' => 'a-random-uuid']];
        $successResponse = new Response(200, [], json_encode($successResponseBody));

        $siriusDocumentUpload = SiriusHelpers::generateSiriusSupportingDocumentUpload(
            $expectedSubmissionIdUsedForSync,
            $this->fileName,
            null,
            $this->s3Reference
        );

        $this->siriusApiGatewayClient
            ->sendSupportingDocument($siriusDocumentUpload, $expectedUuidUsedToSyncDoc, $expectedCaseRefUsedForSync)
            ->shouldBeCalled()
            ->willReturn($successResponse);

        $this->restClient
            ->apiCall(
                'put',
                'document/6789',
                json_encode(['syncStatus' => Document::SYNC_STATUS_SUCCESS]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn(new Document());

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal(),
            $this->fileNameFixer->reveal()
        );

        $sut->syncDocument($queuedDocumentData);
    }

    /** @test */
    public function sendSupportingDocumentReportPdfNotSubmitted()
    {
        $queuedDocumentData = (new QueuedDocumentData())
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
            ->setNdrId(null)
            ->setStorageReference($this->s3Reference);

        $this->restClient
            ->apiCall(
                'put',
                'document/6789',
                json_encode(['syncStatus' => Document::SYNC_STATUS_QUEUED]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal(),
            $this->fileNameFixer->reveal()
        );

        $sut->syncDocument($queuedDocumentData);
    }

    /** @test */
    public function sendSupportingDocumentSyncFailure()
    {
        $queuedDocumentData = (new QueuedDocumentData())
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
            ->setNdrId(null)
            ->setStorageReference($this->s3Reference);

        $failureResponseBody = ['errors' => [0 => ['id' => 'ABC123', 'code' => 'OPGDATA-API-FORBIDDEN']]];
        $failureResponse = new Response(403, [], json_encode($failureResponseBody));

        $requestException = new RequestException('An error occurred', new Request('POST', '/report-submission/9876/update-uuid'), $failureResponse);

        $this->siriusApiGatewayClient->sendSupportingDocument(Argument::cetera())
            ->shouldBeCalled()
            ->willThrow($requestException);

        $this->errorTranslator->translateApiError(json_encode($failureResponseBody))->willReturn(
            'OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions'
        );

        $this->restClient
            ->apiCall(
                'put',
                'document/6789',
                json_encode(
                    ['syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR,
                        'syncError' => 'OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions',
                    ]
                ),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal(),
            $this->fileNameFixer->reveal()
        );

        $sut->syncDocument($queuedDocumentData);

        self::assertNotContains($queuedDocumentData->getReportSubmissionId(), $sut->getSyncErrorSubmissionIds());
    }

    /**
     * @test
     *
     * @dataProvider errorCodeProvider
     */
    public function sendDocumentSyncFailureSiriusErrorTypeBasedOnResponseCode(int $errorCode, string $expectedErrorType, int $syncAttempts)
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
            ->setStorageReference($this->s3Reference)
            ->setFilename($this->fileName)
            ->setIsReportPdf(true)
            ->setCaseNumber('1234567t')
            ->setNdrId(null)
            ->setDocumentSyncAttempts($syncAttempts);

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
        $failureResponse = new Response($errorCode, [], json_encode($failureResponseBody));

        $requestException = new RequestException('An error occurred', new Request('POST', '/report-submission/9876/update-uuid'), $failureResponse);

        $this->siriusApiGatewayClient->sendReportPdfDocument($siriusDocumentUpload, '1234567T')
            ->shouldBeCalled()
            ->willThrow($requestException);

        $this->errorTranslator->translateApiError(json_encode($failureResponseBody))->willReturn(
            'OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions'
        );

        $this->restClient
            ->apiCall(
                'put',
                'document/6789',
                json_encode(
                    ['syncStatus' => $expectedErrorType,
                        'syncError' => 'OPGDATA-API-FORBIDDEN: Credentials used for integration lack correct permissions',
                    ]
                ),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal(),
            $this->fileNameFixer->reveal()
        );

        $sut->syncDocument($queuedDocumentData);
    }

    public function errorCodeProvider()
    {
        return [
            '4XX error code' => [400, Document::SYNC_STATUS_PERMANENT_ERROR, 0],
            '5XX error code' => [500, Document::SYNC_STATUS_TEMPORARY_ERROR, 0],
            '5XX error code - 4th attempt' => [500, Document::SYNC_STATUS_PERMANENT_ERROR, 3],
        ];
    }

    /**
     * @test
     */
    public function sendDocumentInvalidFilenamesAreFixed()
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
            ->setFilename('test .pdf')
            ->setIsReportPdf(false)
            ->setCaseNumber('1234567t')
            ->setNdrId(null)
            ->setStorageReference($this->s3Reference);

        $successResponseBody = ['data' => ['type' => 'supportingDocument', 'id' => 'a-random-uuid']];
        $successResponse = new Response(200, [], json_encode($successResponseBody));

        $siriusDocumentUpload = SiriusHelpers::generateSiriusSupportingDocumentUpload(
            $expectedSubmissionIdUsedForSync,
            'test.pdf',
            null,
            $this->s3Reference
        );

        $this->siriusApiGatewayClient
            ->sendSupportingDocument($siriusDocumentUpload, $expectedUuidUsedToSyncDoc, $expectedCaseRefUsedForSync)
            ->shouldBeCalled()
            ->willReturn($successResponse);

        $this->restClient
            ->apiCall(
                'put',
                'document/6789',
                json_encode(['syncStatus' => Document::SYNC_STATUS_SUCCESS]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn(new Document());

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal(),
            $this->fileNameFixer->reveal()
        );

        $sut->syncDocument($queuedDocumentData);
    }

    /**
     * @test
     */
    public function sendDocumentMissingFileExtensionThrowsError()
    {
        $fileNameFixer = self::prophesize(FileNameFixer::class);
        $fileNameFixer
            ->removeWhiteSpaceBeforeFileExtension('filename-with-no-extension')
            ->willReturn('filename-with-no-extension');

        $document = (new Document())->setId(6789);

        $expectedUuidUsedToSyncDoc = 'report-pdf-submission-uuid';
        $expectedSubmissionIdUsedForSync = 1234;

        $queuedDocumentData = (new QueuedDocumentData())
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
            ->setNdrId(null)
            ->setStorageReference($this->s3Reference);

        $this->siriusApiGatewayClient
            ->sendSupportingDocument(Argument::cetera())
            ->shouldNotBeCalled();

        $this->restClient
            ->apiCall(
                'put',
                'document/6789',
                json_encode(
                    [
                        'syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR,
                        'syncError' => 'File extension is missing from filename. This file will need to be manually synced with Sirius',
                    ]
                ),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService(
            $this->siriusApiGatewayClient->reveal(),
            $this->restClient->reveal(),
            $this->errorTranslator->reveal(),
            $fileNameFixer->reveal()
        );

        $sut->syncDocument($queuedDocumentData);
    }
}
