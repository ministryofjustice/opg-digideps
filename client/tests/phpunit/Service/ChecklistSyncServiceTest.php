<?php declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\Entity\Report\Checklist;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use AppBundle\Exception\PdfGenerationFailedException;
use AppBundle\Exception\SiriusDocumentSyncFailedException;
use AppBundle\Model\Sirius\QueuedChecklistData;
use AppBundle\Model\Sirius\SiriusChecklistPdfDocumentMetadata;
use AppBundle\Model\Sirius\SiriusDocumentFile;
use AppBundle\Model\Sirius\SiriusDocumentUpload;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\Sirius\SiriusApiGatewayClient;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ChecklistSyncServiceTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $restClient;
    private $siriusApiGatewayClient;
    private $errorTranslator;

    /** @var ChecklistSyncService */
    private $sut;

    /** @var QueuedChecklistData */
    private $dataInput;

    /** @var string */
    private $returnValue;

    public function setUp(): void
    {
        $this->restClient = $this->getMockBuilder(RestClient::class)->disableOriginalConstructor()->getMock();
        $this->siriusApiGatewayClient = $this->getMockBuilder(SiriusApiGatewayClient::class)->disableOriginalConstructor()->getMock();
        $this->errorTranslator = $this->getMockBuilder(SiriusApiErrorTranslator::class)->disableOriginalConstructor()->getMock();

        $this->sut = new ChecklistSyncService($this->restClient, $this->siriusApiGatewayClient, $this->errorTranslator);
    }

    /**
     * @test
     */
    public function sendsPostRequestOnFirstSyncOfChecklist()
    {
        $this
            ->buildChecklistDataInput()->withoutChecklistUuid()
            ->assertPostWillBeInvoked()
            ->invokeTest();
    }

    /**
     * @test
     */
    public function postsActualReportUuidForReportsWithAsubmission()
    {
        $this
            ->buildChecklistDataInput()->withoutChecklistUuid()->withReportSubmission()
            ->assertPostWillBeInvokedWithReportUuid()
            ->invokeTest();
    }

    /**
     * @test
     */
    public function postsDummyReportUuidForReportsWithoutAsubmission()
    {
        $this
            ->buildChecklistDataInput()->withoutChecklistUuid()->withoutReportSubmission()
            ->assertPostWillBeInvokedWithFallbackUuid()
            ->invokeTest();
    }

    /**
     * @test
     */
    public function returnsUuidAfterSuccessfulResponse()
    {
        $this
            ->buildChecklistDataInput()->withoutChecklistUuid()
            ->assertPostWillBeInvoked()
            ->invokeTest()
            ->assertUuidIsReturned();
    }

    /**
     * @test
     */
    public function throwsExceptionAfterFailedResponse()
    {
        $expectedException = new SiriusDocumentSyncFailedException('Failed to Sync document');

        $this
            ->buildChecklistDataInput()->withoutChecklistUuid()
            ->ensureFailedPostWillBeInvoked()
            ->expectExceptionObject($expectedException);

        $this->invokeTest();
    }

    private function buildChecklistDataInput(): ChecklistSyncServiceTest
    {
        $this->dataInput = (new QueuedChecklistData())
            ->setCaseNumber('12395438')
            ->setChecklistId(231)
            ->setChecklistFileContents('file-contents')
            ->setReportStartDate(new \DateTime('2020-02-01'))
            ->setreportEndDate(new \DateTime('2021-02-01'))
            ->setReportType('PF')
            ->setSubmitterEmail('a@b.com');

        return $this;
    }

    private function withoutChecklistUuid(): ChecklistSyncServiceTest
    {
        $this->dataInput->setChecklistUuid(null);
        return $this;
    }

    private function withReportSubmission(): ChecklistSyncServiceTest
    {
        $submission = (new ReportSubmission())
            ->setId(1)
            ->setCreatedBy((new User())->setEmail('a@b.com'))
            ->setUuid('rs-uuid');

        $this->dataInput->setReportSubmissions([$submission]);
        return $this;
    }

    private function withoutReportSubmission(): ChecklistSyncServiceTest
    {
        $this->dataInput->setReportSubmissions(null);
        return $this;
    }

    private function assertPostWillBeInvoked(): ChecklistSyncServiceTest
    {
        $this
            ->siriusApiGatewayClient
            ->expects($this->once())
            ->method('postChecklistPdf')
            ->willReturn($this->getSuccessfulResponse());

        return $this;
    }

    private function assertPostWillBeInvokedWithReportUuid(): ChecklistSyncServiceTest
    {
        $this
            ->siriusApiGatewayClient
            ->expects($this->once())
            ->method('postChecklistPdf')
            ->with(
                $this->equalTo($this->buildExpectedUploadObject()),
                $this->dataInput->getSyncedReportSubmission()->getUuid(),
                $this->dataInput->getCaseNumber()
            )
            ->willReturn($this->getSuccessfulResponse());

        return $this;
    }

    private function assertPostWillBeInvokedWithFallbackUuid(): ChecklistSyncServiceTest
    {
        $expectedUploadObject = $this->buildExpectedUploadObject();
        $expectedAttributes = $expectedUploadObject->getAttributes();

        $expectedUploadObject->setAttributes($expectedAttributes->setSubmissionId(null));

        $this
            ->siriusApiGatewayClient
            ->expects($this->once())
            ->method('postChecklistPdf')
            ->with(
                $this->equalTo($expectedUploadObject),
                ChecklistSyncService::PAPER_REPORT_UUID_FALLBACK,
                $this->dataInput->getCaseNumber()
            )
            ->willReturn($this->getSuccessfulResponse());

        return $this;
    }

    private function ensureFailedPostWillBeInvoked(): ChecklistSyncServiceTest
    {
        $this
            ->siriusApiGatewayClient
            ->expects($this->once())
            ->method('postChecklistPdf')
            ->willThrowException(new \Exception('Failed to Sync document'));

        return $this;
    }

    /**
     * @return SiriusDocumentUpload
     */
    private function buildExpectedUploadObject(): SiriusDocumentUpload
    {
        $file = (new SiriusDocumentFile())
            ->setName('checklist-12395438-2020-2021.pdf')
            ->setMimetype('application/pdf')
            ->setSource(base64_encode($this->dataInput->getChecklistFileContents()));

        $attributes = (new SiriusChecklistPdfDocumentMetadata())
            ->setReportingPeriodFrom(new \DateTime('2020-02-01'))
            ->setReportingPeriodTo(new \DateTime('2021-02-01'))
            ->setSubmitterEmail('a@b.com')
            ->setType('PF')
            ->setYear(2021)
            ->setSubmissionId(1);

        return (new SiriusDocumentUpload())
            ->setType('checklists')
            ->setAttributes($attributes)
            ->setFile($file);
    }

    private function assertUuidIsReturned()
    {
        $this->assertEquals('returned-checklist-uuid', $this->returnValue);
    }

    private function invokeTest(): ChecklistSyncServiceTest
    {
        $this->returnValue = $this->sut->sync($this->dataInput);
        return $this;
    }

    /**
     * @return Response
     */
    private function getSuccessfulResponse(): Response
    {
        $successResponseBody = ['data' => ['id' => 'returned-checklist-uuid']];
        $successResponse = new Response('200', [], json_encode($successResponseBody));
        return $successResponse;
    }
}
