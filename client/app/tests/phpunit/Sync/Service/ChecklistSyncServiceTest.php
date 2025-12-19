<?php

declare(strict_types=1);

namespace DigidepsTests\Sync\Service;

use App\Entity\Report\Checklist;
use App\Entity\Report\ReportSubmission;
use App\Entity\User;
use App\Exception\PdfGenerationFailedException;
use App\Exception\SiriusDocumentSyncFailedException;
use App\Model\Sirius\QueuedChecklistData;
use App\Model\Sirius\SiriusChecklistPdfDocumentMetadata;
use App\Model\Sirius\SiriusDocumentFile;
use App\Model\Sirius\SiriusDocumentUpload;
use App\Service\ChecklistPdfGenerator;
use App\Service\Client\RestClient;
use App\Service\Client\Sirius\SiriusApiGatewayClient;
use App\Service\SiriusApiErrorTranslator;
use App\Sync\Service\ChecklistSyncService;
use App\TestHelpers\ChecklistTestHelper;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ChecklistSyncServiceTest extends TestCase
{
    private RestClient $restClient;
    private SiriusApiGatewayClient $siriusApiGatewayClient;
    private SiriusApiErrorTranslator $errorTranslator;
    private QueuedChecklistData $dataInput;
    private string $returnValue;
    private ChecklistPdfGenerator $pdfGenerator;

    private ChecklistSyncService $sut;

    public function setUp(): void
    {
        $this->restClient = $this->getMockBuilder(RestClient::class)->disableOriginalConstructor()->getMock();
        $this->siriusApiGatewayClient = $this->getMockBuilder(SiriusApiGatewayClient::class)->disableOriginalConstructor()->getMock();
        $this->errorTranslator = $this->getMockBuilder(SiriusApiErrorTranslator::class)->disableOriginalConstructor()->getMock();
        $this->pdfGenerator = $this->getMockBuilder(ChecklistPdfGenerator::class)->disableOriginalConstructor()->getMock();

        $this->sut = new ChecklistSyncService($this->restClient, $this->siriusApiGatewayClient, $this->errorTranslator, $this->pdfGenerator);
    }

    public function testSendsPostRequestOnFirstSyncOfChecklist(): void
    {
        $this
            ->buildChecklistDataInput()->withoutChecklistUuid()
            ->assertPostWillBeInvoked()
            ->invokeTest();
    }

    public function testSendsPutRequestOnFirstSyncOfChecklist(): void
    {
        $this
            ->buildChecklistDataInput()->withChecklistUuid()
            ->assertPutWillBeInvoked()
            ->invokeTest();
    }

    public function testPostsActualReportUuidForReportsWithASubmission(): void
    {
        $this
            ->buildChecklistDataInput()->withChecklistUuid()->withReportSubmission()
            ->assertPutWillBeInvokedWithReportUuid()
            ->invokeTest();
    }

    public function testSendsDummyReportUuidForReportsWithoutASubmission(): void
    {
        $this
            ->buildChecklistDataInput()->withoutChecklistUuid()->withoutReportSubmission()
            ->assertPostWillBeInvokedWithFallbackUuid()
            ->invokeTest();
    }

    public function testReturnsUuidAfterSuccessfulResponse(): void
    {
        $this
            ->buildChecklistDataInput()->withChecklistUuid()
            ->assertPutWillBeInvoked()
            ->invokeTest()
            ->assertUuidIsReturned();
    }

    public function testThrowsExceptionAfterFailedResponse(): void
    {
        $expectedException = new SiriusDocumentSyncFailedException('Failed to Sync document');

        $this
            ->buildChecklistDataInput()->withoutChecklistUuid()
            ->ensureFailedPostWillBeInvoked()
            ->expectExceptionObject($expectedException);

        $this->invokeTest();
    }

    private function buildChecklistDataInput(): self
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

    private function withChecklistUuid(): self
    {
        $this->dataInput->setChecklistUuid('cl-uuid');

        return $this;
    }

    private function withoutChecklistUuid(): self
    {
        $this->dataInput->setChecklistUuid(null);

        return $this;
    }

    private function withReportSubmission(): self
    {
        $submission = (new ReportSubmission())
            ->setId(1)
            ->setCreatedBy((new User())->setEmail('a@b.com'))
            ->setUuid('rs-uuid');

        $this->dataInput->setReportSubmissions([$submission]);

        return $this;
    }

    private function withoutReportSubmission(): self
    {
        $this->dataInput->setReportSubmissions(null);

        return $this;
    }

    private function assertPostWillBeInvoked(): self
    {
        $this
            ->siriusApiGatewayClient
            ->expects($this->once())
            ->method('postChecklistPdf')
            ->willReturn($this->getSuccessfulResponse());

        return $this;
    }

    private function assertPutWillBeInvoked(): self
    {
        $this
            ->siriusApiGatewayClient
            ->expects($this->once())
            ->method('putChecklistPdf')
            ->willReturn($this->getSuccessfulResponse());

        return $this;
    }

    private function assertPutWillBeInvokedWithReportUuid(): self
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

    private function assertPostWillBeInvokedWithFallbackUuid(): self
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

    private function ensureFailedPostWillBeInvoked(): self
    {
        $this
            ->siriusApiGatewayClient
            ->expects($this->once())
            ->method('postChecklistPdf')
            ->willThrowException(new \Exception('Failed to Sync document'));

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

    private function assertUuidIsReturned(): void
    {
        $this->assertEquals('returned-checklist-uuid', $this->returnValue);
    }

    private function invokeTest(): self
    {
        $this->returnValue = $this->sut->sync($this->dataInput);

        return $this;
    }

    private function getSuccessfulResponse(): Response
    {
        $successResponseBody = ['data' => ['id' => 'returned-checklist-uuid']];
        return new Response('200', [], json_encode($successResponseBody));
    }

    public function testSyncChecklistsByReportsSyncsMultipleValidChecklists(): void
    {
        $reports = $this->generateSubmittedReports();

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

    public function testSyncChecklistsByReportsChecklistsWithPDFErrorsAreSkipped(): void
    {
        $reports = $this->generateSubmittedReports();

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

    public function testSyncChecklistsByReportsSiriusSyncErrorChecklistsAreSkipped(): void
    {
        $reports = $this->generateSubmittedReports();

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

    private function generateSubmittedReports(): array
    {
        $reports = [];

        foreach (range(1, 2) as $index) {
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
