<?php declare(strict_types=1);

namespace AppBundle\Service;


use AppBundle\Entity\Report\Document;
use AppBundle\Model\Sirius\SiriusDocumentFile;
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
    private $reportSubmittedDate;

    /**@var DateTime */
    private $reportEndDate;

    /**@var DateTime */
    private $reportStartDate;

    /** @var int */
    private $reportSubmissionId;

    /** @var int */
    private $supportingDocSubmissionId;

    /** @var string*/
    private $reportPdfSubmissionUuid;

    /** @var string */
    private $fileContents;

    /** @var string */
    private $fileName;

    /** @var int */
    private $documentId;

    /** @var string */
    private $supportingDocSubmissionUuid;

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
        $this->supportingDocSubmissionId = 9877;
        $this->documentId = 6789;
        $this->reportPdfSubmissionUuid = '5a8b1a26-8296-4373-ae61-f8d0b250e123';
        $this->supportingDocSubmissionUuid = '5a8b1a26-8296-4373-ae61-f8d0b250e321';
        $this->fileContents = '%PDF-1.4\nfake_contents';
        $this->fileName = 'test.pdf';
    }

    /** @test */
    public function syncDocument_report_pdf_sync_success()
    {
        $submittedReportDocument = (new DocumentHelpers())->generateSubmittedReportDocument(
            '1234567T',
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportSubmittedDate,
            $this->fileName,
            $this->reportSubmissionId,
            $this->supportingDocSubmissionId
        );

        $this->s3Storage->retrieve('test')->willReturn($this->fileContents);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportSubmittedDate,
            'PF',
            $this->reportSubmissionId,
            $this->fileName,
            $this->fileContents
        );

        $successResponseBody = ['data' => ['id' => $this->reportPdfSubmissionUuid]];
        $successResponse = new Response('200', [], json_encode($successResponseBody));

        $this->siriusApiGatewayClient->sendReportPdfDocument($siriusDocumentUpload, '1234567T')->shouldBeCalled()->willReturn($successResponse);

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(['data' => ['syncStatus' => Document::SYNC_STATUS_IN_PROGRESS]]),
                Document::class,
                [],
                false
            )
            ->shouldBeCalled();

        $this->restClient
            ->apiCall('put',
                'report-submission/9876/update-uuid',
                json_encode(['data' => ['uuid' => $this->reportPdfSubmissionUuid]]),
                'raw',
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn(new SymfonyResponse('9876'));

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(['data' => ['syncStatus' => Document::SYNC_STATUS_SUCCESS]]),
                Document::class,
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize($submittedReportDocument, 'json'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncDocument($submittedReportDocument);
    }

    /** @test */
    public function sendDocument_sync_failure_sirius()
    {
        $submittedReportDocument = (new DocumentHelpers())->generateSubmittedReportDocument(
            '1234567T',
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportSubmittedDate,
            $this->fileName,
            $this->reportSubmissionId,
            $this->documentId
        );

        $this->s3Storage->retrieve('test')->willReturn($this->fileContents);

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
                json_encode(['data' => ['syncStatus' => Document::SYNC_STATUS_IN_PROGRESS]]),
                Document::class,
                [],
                false
            )
            ->shouldBeCalled();

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(
                    ['data' =>
                        ['syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR, 'syncError' => $failureResponseBody]
                    ]),
                Document::class,
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize($submittedReportDocument, 'json'));;

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncDocument($submittedReportDocument);
    }

    /**
     * @dataProvider s3ErrorProvider
     * @test
     */
    public function sendReportDocument_sync_failure_s3(string $awsErrorCode, string $awsErrorMessage, string $syncStatus)
    {
        $submittedReportDocument = (new DocumentHelpers())->generateSubmittedReportDocument(
            '1234567T',
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportSubmittedDate,
            $this->fileName,
            $this->reportSubmissionId,
            $this->documentId
        );

        $s3Exception = new S3Exception($awsErrorMessage, new Command('getObject'), ['code' => $awsErrorCode]);

        $this->s3Storage->retrieve('test')->willThrow($s3Exception);

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(['data' => ['syncStatus' => Document::SYNC_STATUS_IN_PROGRESS]]),
                Document::class,
                [],
                false
            )
            ->shouldBeCalled();

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(
                    ['data' =>
                        ['syncStatus' => $syncStatus, 'syncError' => 'S3 error while syncing document: ' . $awsErrorMessage]
                    ]),
                Document::class,
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize($submittedReportDocument, 'json'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncDocument($submittedReportDocument);
    }

    public function s3ErrorProvider()
    {
        return [
            'Missing key' => ['NoSuchKey', 'The specified key does not exist.', Document::SYNC_STATUS_PERMANENT_ERROR],
            'Access denied (for deleted items not yet purged)' => ['AccessDenied', 'Access Denied', Document::SYNC_STATUS_PERMANENT_ERROR],
            'Internal error' => ['InternalError', 'We encountered an internal error. Please try again.', Document::SYNC_STATUS_TEMPORARY_ERROR]
        ];
    }

    /** @test */
    public function sendSupportingDocument_success()
    {
        $submittedSupportingDocument = (new DocumentHelpers())->generateSubmittedSupportingDocument(
            '1234567T',
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportSubmittedDate,
            $this->fileName,
            $this->reportSubmissionId,
            $this->supportingDocSubmissionId,
            $this->documentId,
            $this->reportPdfSubmissionUuid
        );

        $this->s3Storage->retrieve('test')->willReturn($this->fileContents);

        $successResponseBody = ['data' => ['type' => 'supportingDocument', 'id' => $this->supportingDocSubmissionUuid]];
        $successResponse = new Response('200', [], json_encode($successResponseBody));

        $siriusDocumentUpload = SiriusHelpers::generateSiriusSupportingDocumentUpload($this->supportingDocSubmissionId, $this->fileName, $this->fileContents);

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(['data' => ['syncStatus' => Document::SYNC_STATUS_IN_PROGRESS]]),
                Document::class,
                [],
                false
            )
            ->shouldBeCalled();

        $this->siriusApiGatewayClient
            ->sendSupportingDocument($siriusDocumentUpload, $this->reportPdfSubmissionUuid, '1234567T')
            ->shouldBeCalled()
            ->willReturn($successResponse);

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(['data' => ['syncStatus' => Document::SYNC_STATUS_SUCCESS]]),
                Document::class,
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize($submittedSupportingDocument, 'json'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncDocument($submittedSupportingDocument);
    }

    /** @test */
    public function sendSupportingDocument_report_pdf_not_submitted()
    {
        $submittedSupportingDocument = (new DocumentHelpers())->generateSubmittedSupportingDocument(
            '1234567T',
            $this->reportStartDate,
            $this->reportEndDate,
            $this->reportSubmittedDate,
            $this->fileName,
            $this->reportSubmissionId,
            $this->supportingDocSubmissionId,
            $this->documentId,
            null
        );

        $this->restClient
            ->apiCall('put',
                'document/6789',
                json_encode(['data' => ['syncStatus' => Document::SYNC_STATUS_QUEUED]]),
                Document::class,
                [],
                false
            )
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize($submittedSupportingDocument, 'json'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncDocument($submittedSupportingDocument);
    }
}
