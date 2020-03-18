<?php declare(strict_types=1);

namespace AppBundle\Service;


use AppBundle\Entity\Report\Document;
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
    }

    /** @test */
    public function sendDocument_report_pdf_sync_success()
    {
        $reportStartDate = new DateTime('2018-05-14');
        $reportEndDate = new DateTime('2019-05-13');
        $reportSubmittedDate = new DateTime('2019-06-20');
        $reportSubmissionId = 9876;
        $supportingDocSubmissionId = 9877;
        $reportPdfSubmissionUuid = '5a8b1a26-8296-4373-ae61-f8d0b250e123';
        $fileContents = 'fake_contents';

        $submittedReportDocument = (new DocumentHelpers())->generateSubmittedReportDocument(
            '1234567T',
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            $reportSubmissionId,
            $supportingDocSubmissionId
        );

        $this->s3Storage->retrieve('test')->willReturn($fileContents);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            'PF',
        );

        $successResponseBody = ['data' => ['id' => $reportPdfSubmissionUuid]];
        $successResponse = new Response('200', [], json_encode($successResponseBody));

        $this->siriusApiGatewayClient->sendReportPdfDocument($siriusDocumentUpload, 'fake_contents', '1234567T')->shouldBeCalled()->willReturn($successResponse);

        $this->restClient->put('report-submission/9876', json_encode(['data' => ['uuid' => $reportPdfSubmissionUuid]]))
            ->shouldBeCalled()
            ->willReturn(new SymfonyResponse('9876'));

        $this->restClient->put('document/6789', json_encode(['data' => ['syncStatus' => Document::SYNC_STATUS_SUCCESS]]))
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize($submittedReportDocument, 'json'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncDocument($submittedReportDocument);
    }

    /** @test */
    public function sendDocument_sync_failure_sirius()
    {
        $reportStartDate = new DateTime('2018-05-14');
        $reportEndDate = new DateTime('2019-05-13');
        $reportSubmittedDate = new DateTime('2019-06-20');
        $reportSubmissionId = 9876;
        $documentId = 6789;
        $fileContents = 'fake_contents';

        $submittedReportDocument = (new DocumentHelpers())->generateSubmittedReportDocument(
            '1234567T',
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            $reportSubmissionId,
            $documentId
        );

        $this->s3Storage->retrieve('test')->willReturn($fileContents);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusReportPdfDocumentUpload(
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            'PF',
            );

        $failureResponseBody = ['errors' => [0 => ['id' => 'ABC123', 'code' => 'OPGDATA-API-FORBIDDEN']]];
        $failureResponse = new Response('403', [], json_encode($failureResponseBody));

        $requestException = new RequestException('An error occurred', new Request('POST', '/report-submission/9876'), $failureResponse);

        $this->siriusApiGatewayClient->sendReportPdfDocument($siriusDocumentUpload, 'fake_contents', '1234567T')->shouldBeCalled()->willThrow($requestException);

        $this->restClient->put('document/6789', json_encode(
            ['data' =>
                ['syncStatus' => Document::SYNC_STATUS_PERMANENT_ERROR, 'syncError' => $failureResponseBody]
            ]))
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize($submittedReportDocument, 'json'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncDocument($submittedReportDocument);
    }

    /**
     * @dataProvider s3ErrorProvider
     * @test
     */
    public function sendReportDocument_sync_failure_s3(string $awsErrorCode, string $awsErrorMessage, string $syncStatus)
    {
        $reportStartDate = new DateTime('2018-05-14');
        $reportEndDate = new DateTime('2019-05-13');
        $reportSubmittedDate = new DateTime('2019-06-20');
        $reportSubmissionId = 9876;
        $documentId = 6789;

        $submittedReportDocument = (new DocumentHelpers())->generateSubmittedReportDocument(
            '1234567T',
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            $reportSubmissionId,
            $documentId
        );

        $s3Exception = new S3Exception($awsErrorMessage, new Command('getObject'), ['code' => $awsErrorCode]);

        $this->s3Storage->retrieve('test')->willThrow($s3Exception);

        $this->restClient->put('document/6789', json_encode(
            ['data' =>
                ['syncStatus' => $syncStatus, 'syncError' => 'S3 error: ' . $awsErrorMessage]
            ]))
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
        $reportStartDate = new DateTime('2018-05-14');
        $reportEndDate = new DateTime('2019-05-13');
        $reportSubmittedDate = new DateTime('2019-06-20');
        $reportPdfSubmissionId = 9876;
        $supportingDocSubmissionId = 9877;
        $documentId = 6789;
        $reportPdfSubmissionUuid = '5a8b1a26-8296-4373-ae61-f8d0b250e123';
        $supportingDocSubmissionUuid = '5a8b1a26-8296-4373-ae61-f8d0b250e321';
        $fileContents = 'fake_contents';

        $submittedSupportingDocument = (new DocumentHelpers())->generateSubmittedSupportingDocument(
            '1234567T',
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            $reportPdfSubmissionId,
            $supportingDocSubmissionId,
            $documentId,
            $reportPdfSubmissionUuid
        );

        $this->s3Storage->retrieve('test')->willReturn($fileContents);

        $successResponseBody = ['data' => ['type' => 'supportingDocument', 'id' => $supportingDocSubmissionUuid]];
        $successResponse = new Response('200', [], json_encode($successResponseBody));

        $siriusDocumentUpload = SiriusHelpers::generateSiriusSupportingDocumentUpload($supportingDocSubmissionId);

        $this->siriusApiGatewayClient
            ->sendSupportingDocument($siriusDocumentUpload, 'fake_contents', $reportPdfSubmissionUuid)
            ->shouldBeCalled()
            ->willReturn($successResponse);

        $this->restClient->put('document/6789', json_encode(['data' => ['syncStatus' => Document::SYNC_STATUS_SUCCESS]]))
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize($submittedSupportingDocument, 'json'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncSupportingDocument($submittedSupportingDocument);
    }

    /** @test */
    public function sendSupportingDocument_report_pdf_not_submitted()
    {
        $reportStartDate = new DateTime('2018-05-14');
        $reportEndDate = new DateTime('2019-05-13');
        $reportSubmittedDate = new DateTime('2019-06-20');
        $reportPdfSubmissionId = 9876;
        $supportingDocSubmissionId = 9877;
        $documentId = 6789;
        $reportPdfSubmissionUuid = null;

        $submittedSupportingDocument = (new DocumentHelpers())->generateSubmittedSupportingDocument(
            '1234567T',
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            $reportPdfSubmissionId,
            $supportingDocSubmissionId,
            $documentId,
            $reportPdfSubmissionUuid
        );

        $this->restClient->put('document/6789', json_encode(
            ['data' =>
                ['syncStatus' => Document::SYNC_STATUS_QUEUED]
            ]))
            ->shouldBeCalled()
            ->willReturn($this->serializer->serialize($submittedSupportingDocument, 'json'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncDocument($submittedSupportingDocument);
    }
}
