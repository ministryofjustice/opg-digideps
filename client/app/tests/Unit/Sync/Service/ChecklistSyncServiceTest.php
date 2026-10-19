<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Sync\Service;

use GuzzleHttp\Psr7\Response;
use OPG\Digideps\Frontend\Entity\Report\Checklist;
use OPG\Digideps\Frontend\Entity\Report\ReportSubmission;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Sync\Exception\PdfGenerationFailedException;
use OPG\Digideps\Frontend\Sync\Exception\SiriusDocumentSyncFailedException;
use OPG\Digideps\Frontend\Sync\Model\Sirius\QueuedChecklistData;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusChecklistPdfDocumentMetadata;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusDocumentFile;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusDocumentUpload;
use OPG\Digideps\Frontend\Sync\Service\ChecklistPdfGenerator;
use OPG\Digideps\Frontend\Sync\Service\ChecklistSyncService;
use OPG\Digideps\Frontend\Sync\Service\Client\Sirius\SiriusApiGatewayClient;
use OPG\Digideps\Frontend\Sync\Service\SiriusApiErrorTranslator;
use OPG\Digideps\Frontend\TestHelpers\ChecklistTestHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChecklistSyncServiceTest extends TestCase
{
    private RestClient&MockObject $restClient;
    private SiriusApiGatewayClient&MockObject $siriusApiGatewayClient;
    private SiriusApiErrorTranslator&MockObject $errorTranslator;
    private ChecklistPdfGenerator&MockObject $pdfGenerator;
    private QueuedChecklistData $dataInput;
    private string $returnValue;

    private ChecklistSyncService $sut;

    public function setUp(): void
    {
        $this->restClient = $this->getMockBuilder(RestClient::class)->disableOriginalConstructor()->getMock();
        $this->siriusApiGatewayClient = $this->getMockBuilder(SiriusApiGatewayClient::class)->disableOriginalConstructor()->getMock();
        $this->errorTranslator = $this->getMockBuilder(SiriusApiErrorTranslator::class)->disableOriginalConstructor()->getMock();
        $this->pdfGenerator = $this->getMockBuilder(ChecklistPdfGenerator::class)->disableOriginalConstructor()->getMock();

        $this->sut = new ChecklistSyncService(
            $this->restClient,
            $this->siriusApiGatewayClient,
            $this->errorTranslator,
            $this->pdfGenerator
        );
    }

    public function testSendsPostRequestOnFirstSyncOfChecklist(): void
    {
        $this->buildChecklistDataInput()
            ->withoutChecklistUuid()
            ->assertPostWillBeInvoked()
            ->invokeTest();
    }

    public function testSendsPutRequestOnFirstSyncOfChecklist(): void
    {
        $this->buildChecklistDataInput()
            ->withChecklistUuid()
            ->assertPutWillBeInvoked()
            ->invokeTest();
    }

    public function testPostsActualReportUuidForReportsWithASubmission(): void
    {
        $this->buildChecklistDataInput()
            ->withChecklistUuid()
            ->withReportSubmission()
            ->assertPutWillBeInvokedWithReportUuid()
            ->invokeTest();
    }

    public function testSendsDummyReportUuidForReportsWithoutASubmission(): void
    {
        $this->buildChecklistDataInput()
            ->withoutChecklistUuid()
            ->withoutReportSubmission()
            ->assertPostWillBeInvokedWithFallbackUuid()
            ->invokeTest();
    }

    public function testReturnsUuidAfterSuccessfulResponse(): void
    {
        $this->buildChecklistDataInput()
            ->withChecklistUuid()
            ->assertPutWillBeInvoked()
            ->invokeTest()
            ->assertUuidIsReturned();
    }

    public function testThrowsExceptionAfterFailedResponse(): void
    {
        $expectedException = new SiriusDocumentSyncFailedException('Failed to Sync document');

        $this->buildChecklistDataInput()
            ->withoutChecklistUuid()
            ->ensureFailedPostWillBeInvoked()
            ->expectExceptionObject($expectedException);

        $this->invokeTest();
    }

    private function buildChecklistDataInput(): static
    {
        $this->dataInput = new QueuedChecklistData()
            ->setCaseNumber('12395438')
            ->setChecklistId(231)
            ->setChecklistFileContents('file-contents')
            ->setReportStartDate(new \DateTime('2020-02-01'))
            ->setreportEndDate(new \DateTime('2021-02-01'))
            ->setReportType('PF')
            ->setSubmitterEmail('a@b.com');

        return $this;
    }

    private function withChecklistUuid(): static
    {
        $this->dataInput->setChecklistUuid('cl-uuid');

        return $this;
    }

    private function withoutChecklistUuid(): static
    {
        $this->dataInput->setChecklistUuid(null);

        return $this;
    }

    private function withReportSubmission(): static
    {
        $submission = new ReportSubmission()
            ->setId(1)
            ->setCreatedBy(new User()->setEmail('a@b.com'))
            ->setUuid('rs-uuid');

        $this->dataInput->setReportSubmissions([$submission]);

        return $this;
    }

    private function withoutReportSubmission(): static
    {
        $this->dataInput->setReportSubmissions(null);

        return $this;
    }

    private function assertPostWillBeInvoked(): static
    {
        $this->siriusApiGatewayClient->expects(self::once())
            ->method('postChecklistPdf')
            ->willReturn($this->createSuccessfulResponse());

        return $this;
    }

    private function assertPutWillBeInvoked(): static
    {
        $this->siriusApiGatewayClient->expects(self::once())
            ->method('putChecklistPdf')
            ->willReturn($this->createSuccessfulResponse());

        return $this;
    }

    private function assertPutWillBeInvokedWithReportUuid(): static
    {
        $this->siriusApiGatewayClient->expects(self::once())
            ->method('putChecklistPdf')
            ->with(
                self::equalTo($this->buildExpectedUploadObject()),
                $this->dataInput->getSyncedReportSubmission()->getUuid(),
                $this->dataInput->getCaseNumber(),
                $this->dataInput->getChecklistUuid()
            )
            ->willReturn($this->createSuccessfulResponse());

        return $this;
    }

    private function assertPostWillBeInvokedWithFallbackUuid(): static
    {
        $expectedUploadObject = $this->buildExpectedUploadObject();
        $expectedAttributes = $expectedUploadObject->getAttributes();

        $expectedUploadObject->setAttributes($expectedAttributes->setSubmissionId(null));

        $this->siriusApiGatewayClient->expects(self::once())
            ->method('postChecklistPdf')
            ->with(
                self::equalTo($expectedUploadObject),
                ChecklistSyncService::PAPER_REPORT_UUID_FALLBACK,
                $this->dataInput->getCaseNumber()
            )
            ->willReturn($this->createSuccessfulResponse());

        return $this;
    }

    private function ensureFailedPostWillBeInvoked(): static
    {
        $this->siriusApiGatewayClient->expects(self::once())
            ->method('postChecklistPdf')
            ->willThrowException(new \Exception('Failed to Sync document'));

        return $this;
    }

    private function buildExpectedUploadObject(?string $source = null): SiriusDocumentUpload
    {
        $encodedSource = $source ? base64_encode($source) : base64_encode($this->dataInput->getChecklistFileContents());

        $file = new SiriusDocumentFile()
            ->setName('checklist-12395438-2020-2021.pdf')
            ->setMimetype('application/pdf')
            ->setSource($encodedSource);

        $attributes = new SiriusChecklistPdfDocumentMetadata()
            ->setReportingPeriodFrom(new \DateTime('2020-02-01'))
            ->setReportingPeriodTo(new \DateTime('2021-02-01'))
            ->setSubmitterEmail('a@b.com')
            ->setType('PF')
            ->setYear(2021)
            ->setSubmissionId(1);

        return new SiriusDocumentUpload()
            ->setType('checklists')
            ->setAttributes($attributes)
            ->setFile($file);
    }

    private function assertUuidIsReturned(): void
    {
        self::assertEquals('returned-checklist-uuid', $this->returnValue);
    }

    private function invokeTest(): static
    {
        $this->returnValue = $this->sut->sync($this->dataInput);

        return $this;
    }

    private function createSuccessfulResponse(): Response
    {
        $successResponseBody = ['data' => ['id' => 'returned-checklist-uuid']];
        return new Response(200, [], json_encode($successResponseBody));
    }

    public function testSyncChecklistsByReportsSyncsMultipleValidChecklists(): void
    {
        $reports = $this->generateSubmittedReports();

        $this->pdfGenerator->expects(self::exactly(2))->method('generate')->willReturn('file-contents');

        $this->siriusApiGatewayClient->expects(self::exactly(2))
            ->method('postChecklistPdf')
            ->with(self::isInstanceOf(SiriusDocumentUpload::class), 'rs-uuid', '12395438')
            ->willReturn($this->createSuccessfulResponse());

        ['notSyncedCount' => $notSyncedCount, 'reportIdsWithNullChecklists' => $reportIdsWithNullChecklist] =
            $this->sut->syncChecklistsByReports($reports);

        self::assertEquals(0, $notSyncedCount, sprintf('Expected $notSyncedCount to be %s, but it was %s', 0, $notSyncedCount));
        self::assertEquals([], $reportIdsWithNullChecklist, sprintf('Expected $reportIdsWithNullChecklist to be %s, but it was %s', json_encode([]), json_encode($reportIdsWithNullChecklist)));
    }

    public function testSyncChecklistsByReportsChecklistsWithPDFErrorsAreSkipped(): void
    {
        $reports = $this->generateSubmittedReports();

        $pdfException = new PdfGenerationFailedException('Failed to sync due to PDF');

        $this->pdfGenerator->expects(self::exactly(2))
            ->method('generate')
            ->will(
                self::onConsecutiveCalls(
                    self::throwException($pdfException),
                    'file-contents',
                )
            );

        $expectedFailureData = json_encode([
            'syncStatus' => Checklist::SYNC_STATUS_PERMANENT_ERROR,
            'syncError' => 'Failed to sync due to PDF',
        ]);

        $expectedSuccessData = json_encode([
            'syncStatus' => Checklist::SYNC_STATUS_SUCCESS,
            'uuid' => 'returned-checklist-uuid',
        ]);

        $invocationMatcher = self::exactly(2);
        $this->restClient->expects($invocationMatcher)
            ->method('apiCall')
            ->willReturnCallback(function (...$options) use ($invocationMatcher, $expectedFailureData, $expectedSuccessData) {
                self::assertEquals('put', $options[0]);
                self::assertEquals('raw', $options[3]);
                self::assertEquals([], $options[4]);
                self::assertFalse($options[5]);

                $invocation = $invocationMatcher->getInvocationCount();
                self::assertEquals("checklist/{$invocation}", $options[1]);

                $actualData = $options[2];
                match ($invocation) {
                    1 => self::assertEquals($expectedFailureData, $actualData),
                    2 => self::assertEquals($expectedSuccessData, $actualData),
                    default => throw new \Exception('Unexpected method invocation'),
                };
            });

        $this->siriusApiGatewayClient->expects(self::once())
            ->method('postChecklistPdf')
            ->with(
                $this->isInstanceOf(SiriusDocumentUpload::class),
                'rs-uuid',
                '12395438',
            )
            ->willReturn($this->createSuccessfulResponse());

        ['notSyncedCount' => $notSyncedCount, 'reportIdsWithNullChecklists' => $reportIdsWithNullChecklist] = $this->sut->syncChecklistsByReports($reports);
        self::assertEquals(1, $notSyncedCount, sprintf('Expected $notSyncedCount to be %s, but it was %s', 0, $notSyncedCount));
        self::assertEquals([], $reportIdsWithNullChecklist, sprintf('Expected $reportIdsWithNullChecklist to be %s, but it was %s', json_encode([]), json_encode($reportIdsWithNullChecklist)));
    }

    public function testSyncChecklistsByReportsSiriusSyncErrorChecklistsAreSkipped(): void
    {
        $reports = $this->generateSubmittedReports();

        $expectedSiriusSyncException = new SiriusDocumentSyncFailedException('Failed to sync due to Sirius sync');

        $this->pdfGenerator->expects(self::exactly(2))
            ->method('generate')
            ->willReturn('file-contents');

        $invocationMatcher = self::exactly(2);
        $this->restClient->expects($invocationMatcher)
            ->method('apiCall')
            ->willReturnCallback(function (...$options) use ($invocationMatcher) {
                $expectedSuccessData = json_encode([
                    'syncStatus' => Checklist::SYNC_STATUS_SUCCESS,
                    'uuid' => 'returned-checklist-uuid',
                ]);

                $expectedFailureData = json_encode([
                    'syncStatus' => Checklist::SYNC_STATUS_PERMANENT_ERROR,
                    'syncError' => 'Failed to sync due to Sirius sync',
                ]);

                self::assertEquals('put', $options[0]);
                self::assertEquals('raw', $options[3]);
                self::assertEquals([], $options[4]);
                self::assertFalse($options[5]);

                $invocation = $invocationMatcher->getInvocationCount();
                self::assertEquals("checklist/{$invocation}", $options[1]);

                $actualData = $options[2];
                match ($invocation) {
                    1 => self::assertEquals($expectedFailureData, $actualData),
                    2 => self::assertEquals($expectedSuccessData, $actualData),
                    default => throw new \Exception('Unexpected method invocation'),
                };
            });

        $this->siriusApiGatewayClient->expects(self::exactly(2))
            ->method('postChecklistPdf')
            ->with(
                self::isInstanceOf(SiriusDocumentUpload::class),
                'rs-uuid',
                '12395438',
            )
            ->will(
                self::onConsecutiveCalls(
                    self::throwException($expectedSiriusSyncException),
                    $this->createSuccessfulResponse(),
                )
            );

        ['notSyncedCount' => $notSyncedCount, 'reportIdsWithNullChecklists' => $reportIdsWithNullChecklist] = $this->sut->syncChecklistsByReports($reports);
        self::assertEquals(1, $notSyncedCount, sprintf('Expected $notSyncedCount to be %s, but it was %s', 1, $notSyncedCount));
        self::assertEquals([], $reportIdsWithNullChecklist, sprintf('Expected $reportIdsWithNullChecklist to be %s, but it was %s', json_encode([]), json_encode($reportIdsWithNullChecklist)));
    }

    private function generateSubmittedReports(): array
    {
        $reports = [];

        foreach (range(1, 2) as $index) {
            $report = ChecklistTestHelper::buildPfaHighReport($index, 'a@b.com', '12395438');

            $submission = new ReportSubmission()
                ->setId(1)
                ->setCreatedBy(new User()->setEmail('a@b.com'))
                ->setUuid('rs-uuid');
            $report->setReportSubmissions([$submission]);

            $reports[] = $report;
        }

        return $reports;
    }
}
