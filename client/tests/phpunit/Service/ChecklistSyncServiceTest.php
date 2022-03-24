<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Report\Checklist;
use App\Entity\Report\ReportSubmission;
use App\Entity\User;
use App\Exception\PdfGenerationFailedException;
use App\Exception\SiriusDocumentSyncFailedException;
use App\Model\Sirius\QueuedChecklistData;
use App\Model\Sirius\SiriusChecklistPdfDocumentMetadata;
use App\Model\Sirius\SiriusDocumentFile;
use App\Model\Sirius\SiriusDocumentUpload;
use App\Service\Client\RestClient;
use App\Service\Client\Sirius\SiriusApiGatewayClient;
use App\TestHelpers\ChecklistTestHelper;
use DateTime;
use Exception;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class ChecklistSyncServiceTest extends TestCase
{
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $restClient;
    private $siriusApiGatewayClient;
    private $errorTranslator;

    /** @var ChecklistSyncService */
    private $sut;

    /** @var QueuedChecklistData */
    private $dataInput;

    /** @var string */
    private $returnValue;

    /** @var ChecklistPdfGenerator|mixed|MockObject */
    private mixed $pdfGenerator;

    public function setUp(): void
    {
        $this->restClient = $this->getMockBuilder(RestClient::class)->disableOriginalConstructor()->getMock();
        $this->siriusApiGatewayClient = $this->getMockBuilder(SiriusApiGatewayClient::class)->disableOriginalConstructor()->getMock();
        $this->errorTranslator = $this->getMockBuilder(SiriusApiErrorTranslator::class)->disableOriginalConstructor()->getMock();
        $this->pdfGenerator = $this->getMockBuilder(ChecklistPdfGenerator::class)->disableOriginalConstructor()->getMock();

        $this->sut = new ChecklistSyncService($this->restClient, $this->siriusApiGatewayClient, $this->errorTranslator, $this->pdfGenerator);
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
    public function sendsPutRequestOnFirstSyncOfChecklist()
    {
        $this
            ->buildChecklistDataInput()->withChecklistUuid()
            ->assertPutWillBeInvoked()
            ->invokeTest();
    }

    /**
     * @test
     */
    public function postsActualReportUuidForReportsWithASubmission()
    {
        $this
            ->buildChecklistDataInput()->withChecklistUuid()->withReportSubmission()
            ->assertPutWillBeInvokedWithReportUuid()
            ->invokeTest();
    }

    /**
     * @test
     */
    public function sendsDummyReportUuidForReportsWithoutASubmission()
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
            ->buildChecklistDataInput()->withChecklistUuid()
            ->assertPutWillBeInvoked()
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
            ->setReportStartDate(new DateTime('2020-02-01'))
            ->setreportEndDate(new DateTime('2021-02-01'))
            ->setReportType('PF')
            ->setSubmitterEmail('a@b.com');

        return $this;
    }

    private function withChecklistUuid(): ChecklistSyncServiceTest
    {
        $this->dataInput->setChecklistUuid('cl-uuid');

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

    private function assertPutWillBeInvoked(): ChecklistSyncServiceTest
    {
        $this
            ->siriusApiGatewayClient
            ->expects($this->once())
            ->method('putChecklistPdf')
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

    private function assertPutWillBeInvokedWithReportUuid(): ChecklistSyncServiceTest
    {
        $this
            ->siriusApiGatewayClient
            ->expects($this->once())
            ->method('putChecklistPdf')
            ->with(
                $this->equalTo($this->buildExpectedUploadObject()),
                $this->dataInput->getSyncedReportSubmission()->getUuid(),
                $this->dataInput->getCaseNumber(),
                $this->dataInput->getChecklistUuid()
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

    private function assertPutWillBeInvokedWithFallbackUuid(): ChecklistSyncServiceTest
    {
        $this
            ->siriusApiGatewayClient
            ->expects($this->once())
            ->method('putChecklistPdf')
            ->with(
                $this->equalTo($this->buildExpectedUploadObject()),
                ChecklistSyncService::PAPER_REPORT_UUID_FALLBACK,
                $this->dataInput->getCaseNumber(),
                $this->dataInput->getChecklistUuid()
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
            ->willThrowException(new Exception('Failed to Sync document'));

        return $this;
    }

    private function ensureFailedPutWillBeInvoked(): ChecklistSyncServiceTest
    {
        $this
            ->siriusApiGatewayClient
            ->expects($this->once())
            ->method('putChecklistPdf')
            ->willThrowException(new Exception('Failed to Sync document'));

        return $this;
    }

    private function buildExpectedUploadObject(?string $source = null): SiriusDocumentUpload
    {
        $encodedSource = $source ? base64_encode($source) : base64_encode($this->dataInput->getChecklistFileContents());

        $file = (new SiriusDocumentFile())
            ->setName('checklist-12395438-2020-2021.pdf')
            ->setMimetype('application/pdf')
            ->setSource($encodedSource);

        $attributes = (new SiriusChecklistPdfDocumentMetadata())
            ->setReportingPeriodFrom(new DateTime('2020-02-01'))
            ->setReportingPeriodTo(new DateTime('2021-02-01'))
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

    private function getSuccessfulResponse(): Response
    {
        $successResponseBody = ['data' => ['id' => 'returned-checklist-uuid']];
        $successResponse = new Response('200', [], json_encode($successResponseBody));

        return $successResponse;
    }

    /**
     * @test
     */
    public function syncChecklistsByReportsSyncsMultipleValidChecklists()
    {
        $reports = $this->generateSubmittedReports(2);

        $this->pdfGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturn('file-contents');

        $this
            ->siriusApiGatewayClient
            ->expects($this->exactly(2))
            ->method('postChecklistPdf')
            ->withConsecutive(
                [
                    $this->isInstanceOf(SiriusDocumentUpload::class),
                    'rs-uuid',
                    '12395438',
                ],
                [
                    $this->isInstanceOf(SiriusDocumentUpload::class),
                    'rs-uuid',
                    '12395438',
                ],
            )
            ->willReturn($this->getSuccessfulResponse());

        $notSyncedCount = $this->sut->syncChecklistsByReports($reports);
        self::assertEquals(0, $notSyncedCount, sprintf('Expected $notSyncedCount to be %s, but it was %s', 0, $notSyncedCount));
    }

    /**
     * @test
     */
    public function syncChecklistsByReportsChecklistsWithPDFErrorsAreSkipped()
    {
        $reports = $this->generateSubmittedReports(2);

        $pdfException = new PdfGenerationFailedException('Failed to sync due to PDF');

        $this->pdfGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->will(
                $this->onConsecutiveCalls(
                    $this->throwException($pdfException),
                    'file-contents',
                )
            );

        $expectedFailureData = [
            'syncStatus' => Checklist::SYNC_STATUS_PERMANENT_ERROR,
            'syncError' => 'Failed to sync due to PDF',
        ];

        $expectedSuccessData = [
            'syncStatus' => Checklist::SYNC_STATUS_SUCCESS,
            'uuid' => 'returned-checklist-uuid',
        ];

        $this->restClient
            ->expects($this->exactly(2))
            ->method('apiCall')
            ->withConsecutive(
                [
                    'put',
                    'checklist/1',
                    json_encode($expectedFailureData),
                    'raw',
                    [],
                    false,
                ],
                [
                    'put',
                    'checklist/2',
                    json_encode($expectedSuccessData),
                    'raw',
                    [],
                    false,
                ]
            );

        $this
            ->siriusApiGatewayClient
            ->expects($this->exactly(1))
            ->method('postChecklistPdf')
            ->withConsecutive(
                [
                    $this->isInstanceOf(SiriusDocumentUpload::class),
                    'rs-uuid',
                    '12395438',
                ],
            )
            ->willReturn($this->getSuccessfulResponse());

        $notSyncedCount = $this->sut->syncChecklistsByReports($reports);
        self::assertEquals(1, $notSyncedCount, sprintf('Expected $notSyncedCount to be %s, but it was %s', 1, $notSyncedCount));
    }

    /**
     * @test
     */
    public function syncChecklistsByReportsSiriusSyncErrorChecklistsAreSkipped()
    {
        $reports = $this->generateSubmittedReports(2);

        $expectedSiriusSyncException = new SiriusDocumentSyncFailedException('Failed to sync due to Sirius sync');

        $this->pdfGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturn('file-contents');

        $expectedFailureData = [
            'syncStatus' => Checklist::SYNC_STATUS_PERMANENT_ERROR,
            'syncError' => 'Failed to sync due to Sirius sync',
        ];

        $expectedSuccessData = [
            'syncStatus' => Checklist::SYNC_STATUS_SUCCESS,
            'uuid' => 'returned-checklist-uuid',
        ];

        $this->restClient
            ->expects($this->exactly(2))
            ->method('apiCall')
            ->withConsecutive(
                [
                    'put',
                    'checklist/1',
                    json_encode($expectedFailureData),
                    'raw',
                    [],
                    false,
                ],
                [
                    'put',
                    'checklist/2',
                    json_encode($expectedSuccessData),
                    'raw',
                    [],
                    false,
                ]
            );

        $this
            ->siriusApiGatewayClient
            ->expects($this->exactly(2))
            ->method('postChecklistPdf')
            ->withConsecutive(
                [
                    $this->isInstanceOf(SiriusDocumentUpload::class),
                    'rs-uuid',
                    '12395438',
                ],
                [
                    $this->isInstanceOf(SiriusDocumentUpload::class),
                    'rs-uuid',
                    '12395438',
                ],
            )
            ->will(
                $this->onConsecutiveCalls(
                    $this->throwException($expectedSiriusSyncException),
                    $this->getSuccessfulResponse(),
                )
            );

        $notSyncedCount = $this->sut->syncChecklistsByReports($reports);
        self::assertEquals(1, $notSyncedCount, sprintf('Expected $notSyncedCount to be %s, but it was %s', 1, $notSyncedCount));
    }

    private function generateSubmittedReports(int $numberOfReports)
    {
        $reports = [];

        foreach (range(1, $numberOfReports) as $index) {
            $report = ChecklistTestHelper::buildPfaHighReport($index, 'a@b.com', '12395438');

            $submission = (new ReportSubmission())
                ->setId(1)
                ->setCreatedBy((new User())->setEmail('a@b.com'))
                ->setUuid('rs-uuid');
            $report->setReportSubmissions([$submission]);

            $reports[] = $report;
        }

        return $reports;
    }
}
