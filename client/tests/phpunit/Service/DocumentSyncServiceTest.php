<?php declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use AppBundle\Service\Client\Sirius\SiriusDocumentFile;
use AppBundle\Service\Client\Sirius\SiriusDocumentMetadata;
use AppBundle\Service\Client\Sirius\SiriusDocumentUpload;
use AppBundle\Service\File\Storage\S3Storage;
use DateTime;
use GuzzleHttp\Psr7\Response;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

        $submittedReportDocument = $this->generateSubmittedReportDocument(
            '1234567T',
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            );

        $this->s3Storage->retrieve('test')->willReturn($fileContents);

        $siriusDocumentUpload = $this->generateSiriusDocumentUpload(
            '1234567T',
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            'PF',
            $fileContents
        );

        $uuid = '5a8b1a26-8296-4373-ae61-f8d0b250e773';
        $successResponseBody = json_encode(['data' => ['uuid' => $uuid]]);
        $successResponse = new Response('200', [], $successResponseBody);

        $this->siriusApiGatewayClient->sendDocument($siriusDocumentUpload)->shouldBeCalled()->willReturn($successResponse);

        $this->restClient->put('report-submission/9876', json_encode(['uuid' => $uuid]))
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

        $submittedReportDocument = $this->generateSubmittedReportDocument(
            '1234567T',
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            );

        $this->s3Storage->retrieve('test')->willReturn($fileContents);

        $siriusDocumentUpload = $this->generateSiriusDocumentUpload(
            '1234567T',
            $reportStartDate,
            $reportEndDate,
            $reportSubmittedDate,
            'PF',
            $fileContents
        );

        $uuid = '5a8b1a26-8296-4373-ae61-f8d0b250e773';
        $successResponseBody = json_encode(['data' => ['uuid' => $uuid]]);
        $successResponse = new Response('200', [], $successResponseBody);

        $this->siriusApiGatewayClient->sendDocument($siriusDocumentUpload)->shouldBeCalled()->willReturn($successResponse);

        $this->restClient->put('report-submission/9876', json_encode(['uuid' => $uuid]))
            ->shouldBeCalled()
            ->willReturn(new SymfonyResponse('9876'));

        $sut = new DocumentSyncService($this->s3Storage->reveal(), $this->siriusApiGatewayClient->reveal(), $this->restClient->reveal());
        $sut->syncReportDocument($submittedReportDocument);
    }

    private function generateSiriusDocumentUpload(
        string $caseRef,
        DateTime $startDate,
        DateTime $endDate,
        DateTime $submittedDate,
        string $orderType,
        string $fileContents,
        string $mimeType = 'application/pdf',
        string $fileName = 'test.pdf'
    )
    {
        $siriusDocumentMetadata = (new SiriusDocumentMetadata())
            ->setReportingPeriodFrom($startDate)
            ->setReportingPeriodTo($endDate)
            ->setYear('2018')
            ->setDateSubmitted($submittedDate)
            ->setOrderType($orderType);

        $siriusDocumentFile = (new SiriusDocumentFile())
            ->setFileName($fileName)
            ->setMimeType($mimeType)
            ->setSource(base64_encode($fileContents));

        return (new SiriusDocumentUpload())
            ->setCaseRef($caseRef)
            ->setDocumentType('Report')
            ->setDocumentSubType('Report')
            ->setDirection('DIRECTION_INCOMING')
            ->setMetadata($siriusDocumentMetadata)
            ->setFile($siriusDocumentFile);
    }

    private function generateSubmittedReportDocument(
        string $caseRef,
        DateTime $startDate,
        DateTime $endDate,
        DateTime $submittedDate,
        string $mimeType = 'application/pdf',
        string $fileName = 'test.pdf',
        string $storageReference = 'test'
    )
    {
        $client = new Client();
        $client->setCaseNumber($caseRef);

        $reportSubmissions = [(new ReportSubmission())->setId(9876)];

        $report = (new Report())
            ->setType(Report::TYPE_102)
            ->setClient($client)
            ->setStartDate($startDate)
            ->setEndDate($endDate)
            ->setSubmitDate($submittedDate)
            ->setReportSubmissions($reportSubmissions);

        $uploadedFile = $this->generateUploadedFile(
            'tests/phpunit/TestData/test.pdf',
            $fileName,
            $mimeType
        );

        return (new Document())
            ->setReport($report)
            ->setStorageReference($storageReference)
            ->setFileName($fileName)
            ->setFile($uploadedFile);
    }

    /**
     * Creates an UploadedFile object based on an existing file in the project.
     *
     * @param string $fileLocation
     * @param string $originalName
     * @param string $mimeType
     * @return UploadedFile
     */
    private function generateUploadedFile(string $fileLocation, string $originalName, string $mimeType)
    {
        //@TODO drop the /../ file path when projectDir works as expected in Symfony 4+
        $projectDir = (self::bootKernel(['debug' => false]))->getProjectDir();
        $location = sprintf('%s/../%s', $projectDir, $fileLocation);

        return new UploadedFile($location, $originalName, $mimeType, null);
    }
}
