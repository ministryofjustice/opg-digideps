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
use Symfony\Component\DependencyInjection\Container;
use Twig\Environment;

class ReportSubmissionServiceTest extends TestCase
{
    protected ReportSubmissionService $sut;

    private MockObject&S3FileUploader $mockFileUploader;
    private MockObject&RestClient $mockRestClient;
    private MockObject&Environment $mockTemplatingEngine;
    private MockObject&HtmlToPdfGenerator $mockPdfGenerator;
    private MockObject&LoggerInterface $mockLogger;
    private MockObject&TransactionsCsvGenerator $mockCsvGenerator;
    private MockObject&Report $mockReport;

    private MockObject&S3FileUploader $fileUploader;
    private MockObject&RestClient $restClient;
    private MockObject&Environment $twig;
    private MockObject&HtmlToPdfGenerator $pdfGenerator;
    private MockObject&LoggerInterface $logger;
    private MockObject&TransactionsCsvGenerator $csvGenerator;

    /**
     * Set up the mockservies.
     */
    public function setUp(): void
    {
        $this->mockFileUploader = $this->createMock(S3FileUploader::class);
        $this->mockRestClient = $this->createMock(RestClient::class);
        $this->mockTemplatingEngine = $this->createMock(Environment::class);
        $this->mockPdfGenerator = $this->createMock(HtmlToPdfGenerator::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);
        $this->mockCsvGenerator = $this->createMock(TransactionsCsvGenerator::class);

        $this->mockReport = $this->createMock(Report::class);

        $this->fileUploader = $this->createMock(S3FileUploader::class);
        $this->restClient = $this->createMock(RestClient::class);
        $this->twig = $this->createMock(Environment::class);
        $this->pdfGenerator = $this->createMock(HtmlToPdfGenerator::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->csvGenerator = $this->createMock(TransactionsCsvGenerator::class);
    }

    /**
     * @dataProvider lowOrNoAssetsReportTypeProvider
     */
    public function testGenerateReportDocumentsWithoutTransactionCsv(string $reportType): void
    {
        $report = $this->createMock(Report::class);
        $report->method('getType')->willReturn($reportType);
        $report->expects($this->atLeastOnce())->method('createAttachmentName')->with('DigiRep-%s_%s_%s.pdf')->willReturn('reportFileName');

        $this->twig->expects($this->atLeastOnce())->method('render')
            ->with(new IsType(IsType::TYPE_STRING), ['report' => $report, 'showSummary' => true])
            ->willReturn('PDF HTML CONTENT');

        $this->pdfGenerator->expects($this->atLeastOnce())->method('getPdfFromHtml')->with('PDF HTML CONTENT')->willReturn('PDF CONTENT');

        $this->fileUploader->method('uploadFileAndPersistDocument')->willReturnMap([[$report, 'PDF CONTENT', 'reportFileName', true, false, $this->createStub(Document::class)]]);

        $sut = $this->generateProphecySut();
        $sut->generateReportDocuments($report);
    }

    private function generateProphecySut(): ReportSubmissionService
    {
        return new ReportSubmissionService(
            $this->csvGenerator,
            $this->twig,
            $this->fileUploader,
            $this->restClient,
            $this->logger,
            $this->pdfGenerator,
        );
    }

    public function lowOrNoAssetsReportTypeProvider(): array
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
        $report = $this->createMock(Report::class);
        $report->method('getType')->willReturn($reportType);
        $report->expects($this->atLeastOnce())->method('getGifts')->willReturn(['a gift']);
        $report->expects($this->exactly(2))->method('createAttachmentName')->willReturnMap([
            ['DigiRep-%s_%s_%s.pdf', 'reportFileName'],
            ['DigiRepTransactions-%s_%s_%s.csv', 'transactionCSVName'],
        ]);

        $this->csvGenerator->expects($this->atLeastOnce())->method('generateTransactionsCsv')->with($report)->willReturn('CSV CONTENT');

        $this->twig->expects($this->atLeastOnce())->method('render')
            ->with(new IsType(IsType::TYPE_STRING), ['report' => $report, 'showSummary' => true])
            ->willReturn('PDF HTML CONTENT');

        $this->pdfGenerator->expects($this->atLeastOnce())->method('getPdfFromHtml')->with('PDF HTML CONTENT')->willReturn('PDF CONTENT');

        $this->fileUploader->expects($this->exactly(2))->method('uploadFileAndPersistDocument')->willReturnMap([
            [$report, 'PDF CONTENT', 'reportFileName', true, false, $this->createStub(Document::class)],
            [$report, 'CSV CONTENT', 'transactionCSVName', false, false, $this->createStub(Document::class)],
        ]);

        $sut = $this->generateProphecySut();
        $sut->generateReportDocuments($report);
    }

    public function highAssetsReportTypeProvider(): array
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

        $this->mockPdfGenerator->expects($this->once())->method('getPdfFromHtml')->with('Report HTML')->willReturn('PDF CONTENT');

        $this->sut = $this->generateSut();

        $this->assertEquals('PDF CONTENT', $this->sut->getPdfBinaryContent($this->mockReport, true));
    }

    /**
     * Generates System Under Test.
     *
     * @return ReportSubmissionService
     */
    private function generateSut(): ReportSubmissionService
    {
        $mockContainer = $this->createMock(Container::class);

        $mockContainer->method('get')->willReturnMap([
            ['file_uploader', $this->mockFileUploader],
            ['rest_client', $this->restClient],
            ['templating', $this->mockTemplatingEngine],
            ['logger', $this->mockLogger],
            ['csv_generator_service', $this->mockCsvGenerator],
        ]);

        return new ReportSubmissionService(
            $this->mockCsvGenerator,
            $this->mockTemplatingEngine,
            $this->mockFileUploader,
            $this->mockRestClient,
            $this->mockLogger,
            $this->mockPdfGenerator
        );
    }

    public function testGetReportSubmissionById(): void
    {
        $id = '123';

        $this->mockRestClient->expects($this->once())->method('get')->with(
            "report-submission/{$id}",
            ReportSubmission::class
        );

        $this->sut = $this->generateSut();
        $this->sut->getReportSubmissionById($id);
    }

    public function testGetReportSubmissionByIds(): void
    {
        $ids = ['123', '456'];

        $reportSubmission1 = new ReportSubmission();
        $reportSubmission1->setId(123);

        $reportSubmission2 = new ReportSubmission();
        $reportSubmission2->setId(456);

        $this->mockRestClient->method('get')->willReturnMap([
            ['report-submission/123', ReportSubmission::class, [], [], $reportSubmission1],
            ['report-submission/456', ReportSubmission::class, [], [], $reportSubmission2],
        ]);

        $this->sut = $this->generateSut();
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

        $this->sut = $this->generateSut();
        $this->sut->assertReportSubmissionIsDownloadable($reportSubmission);
    }

    public function downloadableProvider(): array
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
