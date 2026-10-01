<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service;

use OPG\Digideps\Frontend\Entity\Report\Document;
use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Entity\Report\ReportSubmission;
use OPG\Digideps\Frontend\Exception\ReportSubmissionDocumentsNotDownloadableException;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\Service\Csv\TransactionsCsvGenerator;
use OPG\Digideps\Frontend\Service\File\S3FileUploader;
use OPG\Digideps\Frontend\Service\HtmlToPdfGenerator;
use OPG\Digideps\Frontend\Service\ReportSubmissionService;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class ReportSubmissionServiceTest extends TestCase
{
    private S3FileUploader&MockObject $mockFileUploader;
    private RestClient&MockObject $mockRestClient;
    private Environment&MockObject $mockTemplatingEngine;
    private HtmlToPdfGenerator&MockObject $mockPdfGenerator;
    private TransactionsCsvGenerator&MockObject $mockCsvGenerator;
    private Report&MockObject $mockReport;
    protected ReportSubmissionService $sut;

    public function setUp(): void
    {
        $this->mockFileUploader = self::createMock(S3FileUploader::class);
        $this->mockRestClient = self::createMock(RestClient::class);
        $this->mockTemplatingEngine = self::createMock(Environment::class);
        $this->mockPdfGenerator = self::createMock(HtmlToPdfGenerator::class);
        $this->mockCsvGenerator = self::createMock(TransactionsCsvGenerator::class);
        $this->mockReport = self::createMock(Report::class);

        $this->sut = new ReportSubmissionService(
            $this->mockCsvGenerator,
            $this->mockTemplatingEngine,
            $this->mockFileUploader,
            $this->mockRestClient,
            self::createMock(LoggerInterface::class),
            $this->mockPdfGenerator,
        );
    }

    /**
     * @dataProvider lowOrNoAssetsReportTypeProvider
     */
    public function testGenerateReportDocumentsWithoutTransactionCsv(string $reportType): void
    {
        $report = self::createMock(Report::class);
        $report->expects(self::once())
            ->method('getType')
            ->willReturn($reportType);

        $report->expects(self::atLeastOnce())
            ->method('createAttachmentName')
            ->with('DigiRep-%s_%s_%s.pdf')
            ->willReturn('reportFileName');

        $this->mockTemplatingEngine->expects($this->atLeastOnce())
            ->method('render')
            ->with(new IsType(IsType::TYPE_STRING), ['report' => $report, 'showSummary' => true])
            ->willReturn('PDF HTML CONTENT');

        $this->mockPdfGenerator->expects($this->atLeastOnce())
            ->method('getPdfFromHtml')
            ->with('PDF HTML CONTENT')
            ->willReturn('PDF CONTENT');

        $this->mockFileUploader->method('uploadFileAndPersistDocument')
            ->willReturnMap([
                [$report, 'PDF CONTENT', 'reportFileName', true, false, $this->createStub(Document::class)]
            ]);

        $this->sut->generateReportDocuments($report);
    }

    public static function lowOrNoAssetsReportTypeProvider(): array
    {
        return [
            'Health and Welfare' => [Report::TYPE_HEALTH_WELFARE],
            'Property and Affairs - Low assets' => [Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS],
            'Combined - Low assets' => [Report::TYPE_COMBINED_LOW_ASSETS],
        ];
    }

    /**
     * @dataProvider highAssetsReportTypeProvider
     */
    public function testGenerateReportDocumentsWithTransactionCsv(string $reportType): void
    {
        $report = self::createMock(Report::class);
        $report->expects(self::once())
            ->method('getType')
            ->willReturn($reportType);
        $report->expects(self::once())
            ->method('getGifts')
            ->willReturn(['a gift']);
        $report->expects(self::exactly(2))
            ->method('createAttachmentName')
            ->willReturnMap([
                ['DigiRep-%s_%s_%s.pdf', 'reportFileName'],
                ['DigiRepTransactions-%s_%s_%s.csv', 'transactionCSVName'],
            ]);

        $this->mockCsvGenerator->expects(self::once())
            ->method('generateTransactionsCsv')
            ->with($report)
            ->willReturn('CSV CONTENT');

        $this->mockTemplatingEngine->expects(self::once())
            ->method('render')
            ->with(new IsType(IsType::TYPE_STRING), ['report' => $report, 'showSummary' => true])
            ->willReturn('PDF HTML CONTENT');

        $this->mockPdfGenerator->expects(self::once())
            ->method('getPdfFromHtml')
            ->with('PDF HTML CONTENT')
            ->willReturn('PDF CONTENT');

        $this->mockFileUploader->expects(self::exactly(2))
            ->method('uploadFileAndPersistDocument')
            ->willReturnMap([
                [$report, 'PDF CONTENT', 'reportFileName', true, false, $this->createStub(Document::class)],
                [$report, 'CSV CONTENT', 'transactionCSVName', false, false, $this->createStub(Document::class)],
            ]);

        $this->sut->generateReportDocuments($report);
    }

    public static function highAssetsReportTypeProvider(): array
    {
        return [
            'Property and Affairs - High asserts' => [Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS],
            'Combined - High assets' => [Report::TYPE_COMBINED_HIGH_ASSETS],
        ];
    }

    public function testGetPdfBinaryContent(): void
    {
        $this->mockTemplatingEngine->method('render')
            ->with(
                '@App/Report/Formatted/formatted_standalone.html.twig',
                [
                    'report' => $this->mockReport,
                    'showSummary' => true,
                ]
            )
            ->willReturn('Report HTML');

        $this->mockPdfGenerator->expects(self::once())
            ->method('getPdfFromHtml')
            ->with('Report HTML')
            ->willReturn('PDF CONTENT');

        self::assertEquals('PDF CONTENT', $this->sut->getPdfBinaryContent($this->mockReport, true));
    }

    public function testGetReportSubmissionById(): void
    {
        $id = '123';

        $this->mockRestClient->expects(self::once())
            ->method('get')
            ->with("report-submission/{$id}", ReportSubmission::class);

        $this->sut->getReportSubmissionById($id);
    }

    public function testGetReportSubmissionByIds(): void
    {
        $ids = ['123', '456'];

        $reportSubmission1 = new ReportSubmission();
        $reportSubmission1->setId(123);

        $reportSubmission2 = new ReportSubmission();
        $reportSubmission2->setId(456);

        $this->mockRestClient->method('get')
            ->willReturnMap([
                ['report-submission/123', ReportSubmission::class, [], [], $reportSubmission1],
                ['report-submission/456', ReportSubmission::class, [], [], $reportSubmission2],
            ]);

        $reportSubmissions = $this->sut->getReportSubmissionsByIds($ids);

        self::assertContains($reportSubmission1, $reportSubmissions);
        self::assertContains($reportSubmission2, $reportSubmissions);
    }

    /**
     * @dataProvider downloadableProvider
     */
    public function testAssertReportSubmissionIsDownloadable($reportSubmission): void
    {
        self::expectException(ReportSubmissionDocumentsNotDownloadableException::class);

        $this->sut->assertReportSubmissionIsDownloadable($reportSubmission);
    }

    public static function downloadableProvider(): array
    {
        $unDownloadable = new ReportSubmission();
        $unDownloadable->setDownloadable(false);
        $unDownloadable->setDocuments([new Document()]);

        $missingDocs = new ReportSubmission();
        $missingDocs->setDownloadable(true);
        $missingDocs->setDocuments([]);

        $unDownloadableAndMissingDocs = new ReportSubmission();
        $unDownloadableAndMissingDocs->setDownloadable(false);
        $unDownloadableAndMissingDocs->setDocuments([]);

        return [
            'un-downloadable' => [$unDownloadable],
            'missing docs' => [$missingDocs],
            'un-downloadable and missing docs' => [$unDownloadableAndMissingDocs],
        ];
    }
}
