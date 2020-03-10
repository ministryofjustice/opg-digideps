<?php declare(strict_types=1);

namespace AppBundle\Service;


use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use AppBundle\Service\File\Storage\S3Storage;
use DateTime;
use DigidepsTests\Helpers\DocumentHelpers;
use DigidepsTests\Helpers\SiriusHelpers;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SiriusDocumentsContractTest extends KernelTestCase
{
    /** @var S3Storage&ObjectProphecy $s3Storage */
    private $s3Storage;

    /** @var SiriusApiGatewayClient&ObjectProphecy $siriusApiGatewayClient */
    private $siriusApiGatewayClient;

    /** @var RestClient|ObjectProphecy $restClient */
    private $restClient;

    public function setUp(): void
    {
        /** @var S3Storage&ObjectProphecy $s3Storage */
        $this->s3Storage = self::prophesize(S3Storage::class);

        /** @var SiriusApiGatewayClient&ObjectProphecy $siriusApiGatewayClient */
        $this->siriusApiGatewayClient = self::prophesize(SiriusApiGatewayClient::class);

        /** @var RestClient|ObjectProphecy $restClient */
        $this->restClient = self::prophesize(RestClient::class);
    }

    /** @test */
    public function sendReportDocument_sync_success()
    {
        $reportStartDate = new DateTime('2018-05-14');
        $reportEndDate = new DateTime('2019-05-13');
        $reportSubmittedDate = new DateTime('2019-06-20');
        $fileContents = 'fake_contents';

        $submittedReportDocument = DocumentHelpers::generateSubmittedReportDocument(
            '1234567T',
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            );

        $this->s3Storage->retrieve('test')->willReturn($fileContents);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusDocumentUpload(
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            'PF',
        );

        $uuid = '5a8b1a26-8296-4373-ae61-f8d0b250e773';
        $successResponseBody = json_encode(['data' => ['id' => $uuid]]);
        $successResponse = new Response('200', [], $successResponseBody);

        $this->siriusApiGatewayClient->sendDocument($siriusDocumentUpload, 'fake_contents', '1234567T')->shouldBeCalled()->willReturn($successResponse);

        $this->restClient->put('report-submission/9876', json_encode(['data' => ['response' => json_encode($successResponse->getBody())]]))
            ->shouldBeCalled()
            ->willReturn(new SymfonyResponse('9876'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncReportDocument($submittedReportDocument);
    }

    /** @test */
    public function sendReportDocument_sync_failure()
    {
        $reportStartDate = new DateTime('2018-05-14');
        $reportEndDate = new DateTime('2019-05-13');
        $reportSubmittedDate = new DateTime('2019-06-20');
        $fileContents = 'fake_contents';

        $submittedReportDocument = DocumentHelpers::generateSubmittedReportDocument(
            '1234567T',
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            );

        $this->s3Storage->retrieve('test')->willReturn($fileContents);

        $siriusDocumentUpload = SiriusHelpers::generateSiriusDocumentUpload(
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            'PF',
            );

        $failureResponseBody = json_encode(['errors' => [0 => ['id' => 'ABC123', 'code' => 'OPGDATA-API-FORBIDDEN']]]);
        $failureResponse = new Response('403', [], $failureResponseBody);

        $requestException = new RequestException('An error occurred', new Request('POST', '/report-submission/9876'), $failureResponse);

        $this->siriusApiGatewayClient->sendDocument($siriusDocumentUpload, 'fake_contents', '1234567T')->shouldBeCalled()->willThrow($requestException);

        $this->restClient->put('report-submission/9876', json_encode(['data' => ['response' => $failureResponse->getBody()]]))
            ->shouldBeCalled()
            ->willReturn(new SymfonyResponse('9876'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncReportDocument($submittedReportDocument);
    }
}
