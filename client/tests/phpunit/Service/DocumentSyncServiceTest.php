<?php declare(strict_types=1);

namespace AppBundle\Service;


use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Model\Sirius\QueuedDocumentData;
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

class DocumentSyncServiceTest extends KernelTestCase
{
    /** @var S3Storage&ObjectProphecy $s3Storage */
    private $s3Storage;

    /** @var SiriusApiGatewayClient&ObjectProphecy $siriusApiGatewayClient */
    private $siriusApiGatewayClient;

    /** @var RestClient|ObjectProphecy $restClient */
    private $restClient;

    /** @var Serializer $serializer */
    private $serializer;

    /**@var DateTime */
    private $reportSubmittedDate, $reportEndDate, $reportStartDate;

    /** @var int */
    private $reportSubmissionId, $reportPdfSubmissionUuid, $fileContents, $fileName;

    /** @var int */
    private $documentId;

    public function setUp(): void
    {
        /** @var S3Storage&ObjectProphecy $s3Storage */
        $this->s3Storage = self::prophesize(S3Storage::class);

        /** @var SiriusApiGatewayClient&ObjectProphecy $siriusApiGatewayClient */
        $this->siriusApiGatewayClient = self::prophesize(SiriusApiGatewayClient::class);

        /** @var RestClient|ObjectProphecy $restClient */
        $this->restClient = self::prophesize(RestClient::class);

        /** @var Serializer serializer */
        $this->serializer = (self::bootKernel(['debug' => false]))->getContainer()->get('jms_serializer');

        $this->reportStartDate = new DateTime('2018-05-14');
        $this->reportEndDate = new DateTime('2019-05-13');
        $this->reportSubmittedDate = new DateTime('2019-06-20');
        $this->reportSubmissionId = 9876;
        $this->documentId = 6789;
        $this->reportPdfSubmissionUuid = '5a8b1a26-8296-4373-ae61-f8d0b250e123';
        $this->fileContents = '%PDF-1.4\nfake_contents';
        $this->fileName = 'test.pdf';
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
            ->setStorageReference('storage-ref-here')
            ->setFilename('test.pdf')
            ->setIsReportPdf(true)
            ->setCaseNumber('1234567T')
            ->setNdrId(null);

        $this->s3Storage->retrieve('storage-ref-here')->shouldBeCalled()->willReturn($this->fileContents);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportSubmittedDate,
            $expectedReportType,
            $this->reportSubmissionId,
            $this->fileName,
            $this->fileContents
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

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
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
            ->setStorageReference('storage-ref-here')
            ->setFilename('test.pdf')
            ->setIsReportPdf(true)
            ->setCaseNumber('1234567T')
            ->setNdrId(123);

        $this->s3Storage->retrieve('storage-ref-here')->shouldBeCalled()->willReturn($this->fileContents);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $this->reportStartDate,
            $this->reportStartDate,
            $this->reportSubmittedDate,
            'NDR',
            $this->reportSubmissionId,
            $this->fileName,
            $this->fileContents
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

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
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
            ->setStorageReference('storage-ref-here')
            ->setFilename('test.pdf')
            ->setIsReportPdf(true)
            ->setCaseNumber('1234567T')
            ->setNdrId(null);

        $this->s3Storage->retrieve('storage-ref-here')->willReturn($this->fileContents);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportSubmittedDate,
            'PF',
            $this->reportSubmissionId,
            $this->fileName,
            $this->fileContents
            );

        $failureResponseBody = ['errors' => [0 => ['id' => 'ABC123', 'code' => 'OPGDATA-API-FORBIDDEN']]];
        $failureResponse = new Response('403', [], json_encode($failureResponseBody));

        $requestException = new RequestException('An error occurred', new Request('POST', '/report-submission/9876/update-uuid'), $failureResponse);

        $this->siriusApiGatewayClient->sendReportPdfDocument($siriusDocumentUpload, '1234567T')
            ->shouldBeCalled()
            ->willThrow($requestException);

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(
                    ['syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR,
                    'syncError' => $failureResponseBody
                    ]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncDocument($queuedDocumentData);

        self::assertContains($queuedDocumentData->getReportSubmissionId(), $sut->getSyncErrorSubmissionIds());
    }

    /**
     * @dataProvider s3ErrorProvider
     * @test
     */
    public function sendReportDocument_sync_failure_s3(string $awsErrorCode, string $awsErrorMessage, string $syncStatus, ?int $expectedSubmissionId)
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
            ->setStorageReference('storage-ref-here')
            ->setFilename('test.pdf')
            ->setIsReportPdf(true)
            ->setCaseNumber('1234567T')
            ->setNdrId(null);

        $s3Exception = new S3Exception($awsErrorMessage, new Command('getObject'), ['code' => $awsErrorCode]);

        $this->s3Storage->retrieve('storage-ref-here')->willThrow($s3Exception);

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(
                    ['syncStatus' => $syncStatus,
                    'syncError' => 'S3 error while syncing document: ' . $awsErrorMessage
                    ]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncDocument($queuedDocumentData);

        if ($expectedSubmissionId) {
            self::assertContains($expectedSubmissionId, $sut->getSyncErrorSubmissionIds());
        }
    }

    public function s3ErrorProvider()
    {
        return [
            'Missing key' => ['NoSuchKey', 'The specified key does not exist.', Document::SYNC_STATUS_PERMANENT_ERROR, $this->reportSubmissionId],
            'Access denied (for deleted items not yet purged)' => ['AccessDenied', 'Access Denied', Document::SYNC_STATUS_PERMANENT_ERROR, $this->reportSubmissionId],
            'Internal error' => ['InternalError', 'We encountered an internal error. Please try again.', Document::SYNC_STATUS_TEMPORARY_ERROR, null]
        ];
    }

    /**
     * @test
     * @dataProvider supportingDocumentProivder
     */
    public function sendSupportingDocument_success(
        int $reportPdfSubmissionId,
        int $supportingDocSubmissionId,
        int $expectedIdUsedForSync,
        string $reportPdfUuid,
        string $expectedUuidUsedToSyncDoc
    )
    {
        $reportPdfReportSubmission =
            (new ReportSubmission())
                ->setId($reportPdfSubmissionId)
                ->setUuid($reportPdfUuid);

        $supportingDocSubmission = (new ReportSubmission())->setId($supportingDocSubmissionId);

        $queuedDocumentData = (new QueuedDocumentData())
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId(6789)
            ->setReportSubmissionId($supportingDocSubmissionId)
            ->setReportSubmissions([$reportPdfReportSubmission, $supportingDocSubmission])
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setStorageReference('storage-ref-here')
            ->setFilename('bank-statement.pdf')
            ->setIsReportPdf(false)
            ->setCaseNumber('1234567T')
            ->setNdrId(null);

        $this->s3Storage->retrieve('storage-ref-here')->willReturn($this->fileContents);

        $successResponseBody = ['data' => ['type' => 'supportingDocument', 'id' => 'some-random-uuid']];
        $successResponse = new Response('200', [], json_encode($successResponseBody));

        $siriusDocumentUpload = SiriusHelpers::generateSiriusSupportingDocumentUpload(
            $expectedIdUsedForSync,
            'bank-statement.pdf',
            $this->fileContents
        );

        $this->siriusApiGatewayClient
            ->sendSupportingDocument($siriusDocumentUpload, $expectedUuidUsedToSyncDoc, '1234567T')
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

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncDocument($queuedDocumentData);
    }

    public function supportingDocumentProivder()
    {
        return [
            'Sent with report PDF' => [1234, 1234, 1234, 'report-pdf-uuid', 'report-pdf-uuid'],
            'Sent after report PDF' => [1234, 4321, 4321, 'report-pdf-uuid', 'report-pdf-uuid']
        ];
    }

    /** @test */
    public function sendSupportingDocument_report_pdf_not_submitted()
    {
        $supportingDocSubmission = (new ReportSubmission())->setId($this->reportSubmissionId);

        $queuedDocumentData = (new QueuedDocumentData())
            ->setReportType(Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS)
            ->setDocumentId(6789)
            ->setReportSubmissionId($this->reportSubmissionId)
            ->setReportSubmissions([$supportingDocSubmission])
            ->setReportStartDate($this->reportStartDate)
            ->setReportEndDate($this->reportEndDate)
            ->setReportSubmitDate($this->reportSubmittedDate)
            ->setStorageReference('storage-ref-here')
            ->setFilename('bank-statement.pdf')
            ->setIsReportPdf(false)
            ->setCaseNumber('1234567T')
            ->setNdrId(null);

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

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncDocument($queuedDocumentData);
    }

    /** @test */
    public function sendSupportingDocument_sync_failure()
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
            ->setStorageReference('storage-ref-here')
            ->setFilename('bank-statement.pdf')
            ->setIsReportPdf(false)
            ->setCaseNumber('1234567T')
            ->setNdrId(null);

        $this->s3Storage->retrieve('storage-ref-here')->willReturn($this->fileContents);

        $failureResponseBody = ['errors' => [0 => ['id' => 'ABC123', 'code' => 'OPGDATA-API-FORBIDDEN']]];
        $failureResponse = new Response('403', [], json_encode($failureResponseBody));

        $requestException = new RequestException('An error occurred', new Request('POST', '/report-submission/9876/update-uuid'), $failureResponse);

        $this->siriusApiGatewayClient->sendSupportingDocument(Argument::cetera())
            ->shouldBeCalled()
            ->willThrow($requestException);

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(
                    ['syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR,
                        'syncError' => $failureResponseBody
                    ]),
                'Report\\Document',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize(new Document(), 'json'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncDocument($queuedDocumentData);

        self::assertNotContains($queuedDocumentData->getReportSubmissionId(), $sut->getSyncErrorSubmissionIds());
    }

    /**
     * @test
     * @dataProvider errorProvider
     */
    public function translateApiErrors(?string $apiErrorCode, string $expectedTranslation)
    {
        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());

        $translation = $sut->translateApiError($apiErrorCode);
        $expectedError = sprintf('%s: %s', $apiErrorCode, $expectedTranslation);

        self::assertEquals($expectedError, $translation);
    }

    public function errorProvider()
    {
        return [
            'ACCESS_DENIED' => ['OPGDATA-API-FORBIDDEN', 'Credentials used for integration lack correct permissions'],
            'API_CONFIGURATION_ERROR' => ['OPGDATA-API-API_CONFIGURATION_ERROR', 'Integration API internal error'],
            'AUTHORIZER_CONFIGURATION_ERROR' => ['OPGDATA-API-AUTHORIZER_CONFIGURATION_ERROR', 'Integration API internal error'],
            'AUTHORIZER_FAILURE' => ['OPGDATA-API-AUTHORIZER_FAILURE', 'Integration API internal error'],
            'BAD_REQUEST_BODY' => ['OPGDATA-API-INVALIDREQUEST', 'The body of the request is not valid'],
            'BAD_REQUEST_PARAMETERS' => ['OPGDATA-API-BAD_REQUEST_PARAMETERS', 'The parameters of the request are not valid'],
            'DEFAULT_5XX' => ['OPGDATA-API-SERVERERROR', 'Integration API server error'],
            'EXPIRED_TOKEN' => ['OPGDATA-API-EXPIRED_TOKEN', 'Auth token has expired'],
            'INTEGRATION_FAILURE' => ['OPGDATA-API-INTEGRATION_FAILURE', 'There was a problem syncing from the integration to Sirius'],
            'INTEGRATION_TIMEOUT' => ['OPGDATA-API-INTEGRATION_TIMEOUT', 'The sync process timed out while communicating with Sirius'],
            'INVALID_API_KEY' => ['OPGDATA-API-INVALID_API_KEY', 'The API key used in the request is not valid'],
            'INVALID_SIGNATURE' => ['OPGDATA-API-INVALID_SIGNATURE', 'The signature of the request is not valid'],
            'MISSING_AUTHENTICATION_TOKEN' => ['OPGDATA-API-MISSING_AUTHENTICATION_TOKEN', 'Authentication token is missing from the request'],
            'QUOTA_EXCEEDED' => ['OPGDATA-API-QUOTA_EXCEEDED', 'API quota has been exceeded'],
            'REQUEST_TOO_LARGE' => ['OPGDATA-API-FILESIZELIMIT', 'The size of the file exceeded the file size limit (6MB)'],
            'RESOURCE_NOT_FOUND' => ['OPGDATA-API-NOTFOUND', 'Invalid URL used during integration or the resource no longer exists'],
            'THROTTLED' => ['OPGDATA-API-THROTTLED', 'Too many requests made - throttling in action'],
            'UNAUTHORIZED' => ['OPGDATA-API-UNAUTHORISED', 'No user/auth provided during requests'],
            'UNSUPPORTED_MEDIA_TYPE' => ['OPGDATA-API-MEDIA', 'Media type of the file is not supported'],
            'WAF_FILTERED' => ['OPGDATA-API-WAF_FILTERED', 'AWS WAF filtered this request and it was not sent to Sirius'],
        ];
    }

    /**
     * @test
     */
    public function translateApiErrors_unexpected_error_code()
    {
        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());

        $translation = $sut->translateApiError("SOME ERROR CODE WE HAVEN'T SEEN BEFORE");
        $expectedError = 'UNEXPECTED ERROR CODE: An unknown error occurred during document sync';

        self::assertEquals($expectedError, $translation);
    }
}
