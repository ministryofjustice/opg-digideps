<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\User;
use AppBundle\Exception\ReportSubmissionDocumentsNotDownloadableException;
use AppBundle\Model\Email;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\FileUploader;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\TestHelpers\ReportTestHelper;
use MockeryStub as m;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;
use Twig\Environment;

class ReportSubmissionServiceTest extends TestCase
{
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

    /** @var ObjectProphecy&FileUploader */
    private $fileUploader;
    /** @var ObjectProphecy&RestClient */
    private $restClient;
    /** @var ObjectProphecy&MailSender */
    private $mailSender;
    /** @var ObjectProphecy&MailFactory */
    private $mailFactory;
    /** @var ObjectProphecy&Environment */
    private $twig;
    /** @var ObjectProphecy&WkHtmlToPdfGenerator */
    private $pdfGenerator;
    /** @var ObjectProphecy&Logger */
    private $logger;
    /** @var ObjectProphecy&CsvGeneratorService */
    private $csvGenerator;

    /**
     * Set up the mockservies
     */
    public function setUp(): void
    {
        $this->mockFileUploader = m::mock(FileUploader::class);
        $this->mockRestClient = m::mock(RestClient::class);
        $this->mockMailSender = m::mock(MailSender::class);
        $this->mockMailFactory = m::mock(MailFactory::class);
        $this->mockTemplatingEngine = m::mock(Environment::class);
        $this->mockPdfGenerator = m::mock(WkHtmlToPdfGenerator::class);
        $this->mockLogger = m::mock(Logger::class);
        $this->mockCsvGenerator = m::mock(CsvGeneratorService::class);

        $this->mockReport = m::mock(ReportInterface::class);

        $this->fileUploader = self::prophesize(FileUploader::class);
        $this->restClient = self::prophesize(RestClient::class);
        $this->mailSender = self::prophesize(MailSender::class);
        $this->mailFactory = self::prophesize(MailFactory::class);
        $this->twig = self::prophesize(Environment::class);
        $this->pdfGenerator = self::prophesize(WkHtmlToPdfGenerator::class);
        $this->logger = self::prophesize(Logger::class);
        $this->csvGenerator = self::prophesize(CsvGeneratorService::class);
    }

    /**
     * Generates System Under Test
     *
     * @return ReportSubmissionService
     */
    private function generateSut()
    {
        $mockContainer = m::mock(Container::class);

        $mockContainer->shouldReceive('get')->with('file_uploader')->andReturn($this->mockFileUploader);
        $mockContainer->shouldReceive('get')->with('rest_client')->andReturn($this->mockRestClient);
        $mockContainer->shouldReceive('get')->with('AppBundle\Service\Mailer\MailSender')->andReturn($this->mockMailSender);
        $mockContainer->shouldReceive('get')->with('AppBundle\Service\Mailer\MailFactory')->andReturn($this->mockMailFactory);
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

    /**
     * @test
     * @dataProvider lowOrNoAssetsReportTypeProvider
     */
    public function generateReportDocuments_without_transaction_csv(string $reportType)
    {
        $report = self::prophesize(Report::class);
        $report->getType()->willReturn($reportType);
        $report->createAttachmentName('DigiRep-%s_%s_%s.pdf')->shouldBeCalled()->willReturn('reportFileName');

        $this->twig->render(Argument::type('string'), ['report' => $report, 'showSummary' => Argument::type('bool')])
            ->shouldBeCalled()
            ->willReturn('PDF HTML CONTENT');

        $this->pdfGenerator->getPdfFromHtml('PDF HTML CONTENT')->shouldBeCalled()->willReturn('PDF CONTENT');

        $this->fileUploader->uploadFile($report, 'PDF CONTENT', 'reportFileName', true)->shouldBeCalled();
        $this->fileUploader->uploadFile($report, Argument::type('string'), Argument::type('string'), false)->shouldNotBeCalled();

        $sut = $this->generateProphecySut();
        $sut->generateReportDocuments($report->reveal());
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
     * @dataProvider HighAssetsReportTypeProvider
     */
    public function generateReportDocuments_with_transaction_csv(string $reportType)
    {
        $report = self::prophesize(Report::class);
        $report->getType()->willReturn($reportType);
        $report->createAttachmentName('DigiRep-%s_%s_%s.pdf')->shouldBeCalled()->willReturn('reportFileName');
        $report->createAttachmentName('DigiRepTransactions-%s_%s_%s.csv')->shouldBeCalled()->willReturn('transactionCSVName');

        $this->csvGenerator->generateTransactionsCsv($report)->shouldBeCalled()->willReturn('CSV CONTENT');

        $this->twig->render(Argument::type('string'), ['report' => $report, 'showSummary' => Argument::type('bool')])
            ->shouldBeCalled()
            ->willReturn('PDF HTML CONTENT');

        $this->pdfGenerator->getPdfFromHtml('PDF HTML CONTENT')->shouldBeCalled()->willReturn('PDF CONTENT');

        $this->fileUploader->uploadFile($report, 'PDF CONTENT', 'reportFileName', true)->shouldBeCalled();
        $this->fileUploader->uploadFile($report, 'CSV CONTENT', 'transactionCSVName', false)->shouldBeCalled();

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
                'AppBundle:Report/Formatted:formatted_standalone.html.twig',
                [
                    'report' => $this->mockReport,
                    'showSummary' => true
                ]
            )
            ->andReturn('Report HTML');

        $this->mockPdfGenerator->shouldReceive('getPdfFromHtml')->with('Report HTML')->once()->andReturn('PDF CONTENT');

        $this->sut = $this->generateSut();

        $this->assertEquals('PDF CONTENT', $this->sut->getPdfBinaryContent($this->mockReport, true));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testFirstTimeSubmitForDeputyOrg()
    {
        $reportId = 99;
        $newYearReportId = $reportId + 1;

        $newYearReport = m::mock(Report::class);

        $this->mockReport->shouldReceive('getId')->andReturn($reportId);
        $this->mockRestClient->shouldReceive('put')->with(
            'report/' . $reportId . '/submit',
            $this->mockReport,
            ['submit']
        )->andReturn($newYearReportId);

        $this->mockRestClient->shouldReceive('get')->with(
            'report/' . $newYearReportId,
            'Report\\Report'
        )->andReturn($newYearReport);

        $mockUser = m::mock(User::class);

        $mockEmail  = m::mock(Email::class);
        $this->mockMailFactory->shouldReceive('createReportSubmissionConfirmationEmail')
            ->once()
            ->with($mockUser, $this->mockReport, $newYearReport)
            ->andReturn($mockEmail);

        $this->mockMailSender->shouldReceive('send')
            ->once()
            ->with($mockEmail);

        $this->sut = $this->generateSut();

        $this->sut->submit($this->mockReport, $mockUser);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSubsequentSubmitAllDeputies()
    {
        $reportId = 99;

        $mockUser = m::mock(User::class);

        $this->mockReport->shouldReceive('getId')->andReturn($reportId);
        $this->mockRestClient->shouldReceive('put')->with(
            'report/' . $reportId . '/submit',
            $this->mockReport,
            ['submit']
        )->andReturnNull();

        $this->sut = $this->generateSut();

        $this->sut->submit($this->mockReport, $mockUser);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testFirstTimeSubmitForNonDeputyOrg()
    {
        $reportId = 99;
        $newYearReportId = $reportId + 1;

        $newYearReport = m::mock(Report::class);

        $this->mockReport->shouldReceive('getId')->andReturn($reportId);
        $this->mockRestClient->shouldReceive('put')->with(
            'report/' . $reportId . '/submit',
            $this->mockReport,
            ['submit']
        )->andReturn($newYearReportId);

        $this->mockRestClient->shouldReceive('get')->with(
            'report/' . $newYearReportId,
            'Report\\Report'
        )->andReturn($newYearReport);

        $mockUser = m::mock(User::class);

        $mockEmail  = m::mock(Email::class);
        $this->mockMailFactory->shouldReceive('createReportSubmissionConfirmationEmail')
            ->once()
            ->with($mockUser, $this->mockReport, $newYearReport)
            ->andReturn($mockEmail);

        $this->mockMailSender->shouldReceive('send')
            ->once()
            ->with($mockEmail);

        $this->sut = $this->generateSut();

        $this->sut->submit($this->mockReport, $mockUser);
    }


    /**
     * @doesNotPerformAssertions
     */
    public function testGetReportSubmissionById()
    {
        $id = '123';

        $this->mockRestClient->shouldReceive('get')->once()->with(
            "report-submission/${id}",
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
            "report-submission/123",
            'Report\\ReportSubmission'
        )->andReturn($reportSubmission1);

        $this->mockRestClient->shouldReceive('get')->with(
            "report-submission/456",
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
