<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\ReportInterface;
use App\Exception\ReportSubmissionDocumentsNotDownloadableException;
use App\Service\Client\RestClient;
use App\Service\Csv\TransactionsCsvGenerator;
use App\Service\File\S3FileUploader;
use App\Service\Mailer\MailFactory;
use App\Service\Mailer\MailSender;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Container;
use Twig\Environment;

class ReportSubmissionServiceTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ReportSubmissionService
     */
    protected $sut;

    private $mockFileUploader;
    private $mockRestClient;
    private $mockMailSender;
    private $mockMailFactory;
    private $mockTemplatingEngine;
    private $mockPdfGenerator;
    private $mockLogger;
    private $mockCsvGenerator;
    private $mockReport;

    /** @var ObjectProphecy&S3FileUploader */
    private $fileUploader;
    /** @var ObjectProphecy&RestClient */
    private $restClient;
    /** @var ObjectProphecy&MailSender */
    private $mailSender;
    /** @var ObjectProphecy&MailFactory */
    private $mailFactory;
    /** @var ObjectProphecy&Environment */
    private $twig;
    /** @var ObjectProphecy&HtmlToPdfGenerator */
    private $pdfGenerator;
    /** @var ObjectProphecy&LoggerInterface */
    private $logger;
    /** @var ObjectProphecy&TransactionsCsvGenerator */
    private $csvGenerator;

    /**
     * Set up the mockservies.
     */
    public function setUp(): void
    {
        $this->mockFileUploader = m::mock(S3FileUploader::class);
        $this->mockRestClient = m::mock(RestClient::class);
        $this->mockMailSender = m::mock(MailSender::class);
        $this->mockMailFactory = m::mock(MailFactory::class);
        $this->mockTemplatingEngine = m::mock(Environment::class);
        $this->mockPdfGenerator = m::mock(HtmlToPdfGenerator::class);
        $this->mockLogger = m::mock(LoggerInterface::class);
        $this->mockCsvGenerator = m::mock(TransactionsCsvGenerator::class);

        $this->mockReport = m::mock(ReportInterface::class);

        $this->fileUploader = self::prophesize(S3FileUploader::class);
        $this->restClient = self::prophesize(RestClient::class);
        $this->mailSender = self::prophesize(MailSender::class);
        $this->mailFactory = self::prophesize(MailFactory::class);
        $this->twig = self::prophesize(Environment::class);
        $this->pdfGenerator = self::prophesize(HtmlToPdfGenerator::class);
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->csvGenerator = self::prophesize(TransactionsCsvGenerator::class);
    }

    /**
     * @test
     *
     * @dataProvider lowOrNoAssetsReportTypeProvider
     */
    public function generateReportDocumentsWithoutTransactionCsv(string $reportType)
    {
        $report = self::prophesize(Report::class);
        $report->getType()->willReturn($reportType);
        $report->createAttachmentName('DigiRep-%s_%s_%s.pdf')->shouldBeCalled()->willReturn('reportFileName');

        $this->twig->render(Argument::type('string'), ['report' => $report, 'showSummary' => Argument::type('bool')])
            ->shouldBeCalled()
            ->willReturn('PDF HTML CONTENT');

        $this->pdfGenerator->getPdfFromHtml('PDF HTML CONTENT')->shouldBeCalled()->willReturn('PDF CONTENT');

        $this->fileUploader->uploadFileAndPersistDocument($report, 'PDF CONTENT', 'reportFileName', true, false)->shouldBeCalled();
        $this->fileUploader->uploadFileAndPersistDocument($report, Argument::type('string'), Argument::type('string'), false, false)->shouldNotBeCalled();

        $sut = $this->generateProphecySut();
        $sut->generateReportDocuments($report->reveal());
    }

    private function generateProphecySut()
    {
        return new ReportSubmissionService(
            $this->csvGenerator->reveal(),
            $this->twig->reveal(),
            $this->fileUploader->reveal(),
            $this->restClient->reveal(),
            $this->logger->reveal(),
            $this->mailFactory->reveal(),
            $this->mailSender->reveal(),
            $this->pdfGenerator->reveal(),
        );
    }

    public function lowOrNoAssetsReportTypeProvider()
    {
        return [
            'Health and Welfare' => [Report::TYPE_HEALTH_WELFARE],
            'Property and Affairs - Low assets' => [Report::TYPE_PROPERTY_AND_AFFAIRS_LOW_ASSETS],
            'Combined - Low assets' => [Report::TYPE_COMBINED_LOW_ASSETS],
        ];
    }

    /**
     * @test
     *
     * @dataProvider HighAssetsReportTypeProvider
     */
    public function generateReportDocumentsWithTransactionCsv(string $reportType)
    {
        $report = self::prophesize(Report::class);
        $report->getType()->willReturn($reportType);
        $report->getGifts()->shouldBeCalled()->willReturn(['a gift']);
        $report->createAttachmentName('DigiRep-%s_%s_%s.pdf')->shouldBeCalled()->willReturn('reportFileName');
        $report->createAttachmentName('DigiRepTransactions-%s_%s_%s.csv')->shouldBeCalled()->willReturn('transactionCSVName');

        $this->csvGenerator->generateTransactionsCsv($report)->shouldBeCalled()->willReturn('CSV CONTENT');

        $this->twig->render(Argument::type('string'), ['report' => $report, 'showSummary' => Argument::type('bool')])
            ->shouldBeCalled()
            ->willReturn('PDF HTML CONTENT');

        $this->pdfGenerator->getPdfFromHtml('PDF HTML CONTENT')->shouldBeCalled()->willReturn('PDF CONTENT');

        $this->fileUploader->uploadFileAndPersistDocument($report, 'PDF CONTENT', 'reportFileName', true, false)->shouldBeCalled();
        $this->fileUploader->uploadFileAndPersistDocument($report, 'CSV CONTENT', 'transactionCSVName', false)->shouldBeCalled();

        $sut = $this->generateProphecySut();
        $sut->generateReportDocuments($report->reveal());
    }

    public function HighAssetsReportTypeProvider()
    {
        return [
            'Property and Affairs - High asserts' => [Report::TYPE_PROPERTY_AND_AFFAIRS_HIGH_ASSETS],
            'Combined - High assets' => [Report::TYPE_COMBINED_HIGH_ASSETS],
        ];
    }

    public function testGetPdfBinaryContent()
    {
        $this->mockTemplatingEngine->shouldReceive('render')
            ->with(
                '@App/Report/Formatted/formatted_standalone.html.twig',
                [
                    'report' => $this->mockReport,
                    'showSummary' => true,
                ]
            )
            ->andReturn('Report HTML');

        $this->mockPdfGenerator->shouldReceive('getPdfFromHtml')->with('Report HTML')->once()->andReturn('PDF CONTENT');

        $this->sut = $this->generateSut();

        $this->assertEquals('PDF CONTENT', $this->sut->getPdfBinaryContent($this->mockReport, true));
    }

    /**
     * Generates System Under Test.
     *
     * @return ReportSubmissionService
     */
    private function generateSut()
    {
        $mockContainer = m::mock(Container::class);

        $mockContainer->shouldReceive('get')->with('file_uploader')->andReturn($this->mockFileUploader);
        $mockContainer->shouldReceive('get')->with('rest_client')->andReturn($this->mockRestClient);
        $mockContainer->shouldReceive('get')->with('App\Service\Mailer\MailSender')->andReturn($this->mockMailSender);
        $mockContainer->shouldReceive('get')->with('App\Service\Mailer\MailFactory')->andReturn($this->mockMailFactory);
        $mockContainer->shouldReceive('get')->with('templating')->andReturn($this->mockTemplatingEngine);
        $mockContainer->shouldReceive('get')->with('logger')->andReturn($this->mockLogger);
        $mockContainer->shouldReceive('get')->with('csv_generator_service')->andReturn($this->mockCsvGenerator);

        return new ReportSubmissionService(
            $this->mockCsvGenerator,
            $this->mockTemplatingEngine,
            $this->mockFileUploader,
            $this->mockRestClient,
            $this->mockLogger,
            $this->mockMailFactory,
            $this->mockMailSender,
            $this->mockPdfGenerator
        );
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testGetReportSubmissionById()
    {
        $id = '123';

        $this->mockRestClient->shouldReceive('get')->once()->with(
            "report-submission/{$id}",
            'Report\\ReportSubmission'
        );

        $this->sut = $this->generateSut();
        $this->sut->getReportSubmissionById($id);
    }

    public function testGetReportSubmissionByIds()
    {
        $ids = ['123', '456'];

        $reportSubmission1 = new ReportSubmission();
        $reportSubmission1->setId(123);

        $reportSubmission2 = new ReportSubmission();
        $reportSubmission2->setId(456);

        $this->mockRestClient->shouldReceive('get')->with(
            'report-submission/123',
            'Report\\ReportSubmission'
        )->andReturn($reportSubmission1);

        $this->mockRestClient->shouldReceive('get')->with(
            'report-submission/456',
            'Report\\ReportSubmission'
        )->andReturn($reportSubmission2);

        $this->sut = $this->generateSut();
        $reportSubmissions = $this->sut->getReportSubmissionsByIds($ids);

        self::assertContains($reportSubmission1, $reportSubmissions);
        self::assertContains($reportSubmission2, $reportSubmissions);
    }

    /**
     * @dataProvider downloadableProvider
     */
    public function testAssertReportSubmissionIsDownloadable($reportSubmission)
    {
        self::expectException(ReportSubmissionDocumentsNotDownloadableException::class);

        $this->sut = $this->generateSut();
        $this->sut->assertReportSubmissionIsDownloadable($reportSubmission);
    }

    public function downloadableProvider()
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
