<?php

namespace AppBundle\Service;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\User;
use AppBundle\Model\Email;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\FileUploader;
use AppBundle\Service\File\Storage\S3Storage;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Exception;
use MockeryStub as m;
use Olcs\Logging\Log\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\Container;

class ReportSubmissionServiceTest extends MockeryTestCase
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
    private $mockTranslator;
    private $mockLogger;
    private $mockCsvGenerator;
    private $mockReport;

    /**
     * Set up the mockservies
     */
    public function setUp()
    {
        $this->mockFileUploader = m::mock(FileUploader::class);
        $this->mockRestClient = m::mock(RestClient::class);
        $this->mockMailSender = m::mock(MailSender::class);
        $this->mockMailFactory = m::mock(MailFactory::class);
        $this->mockTemplatingEngine = m::mock(TwigEngine::class);
        $this->mockPdfGenerator = m::mock(WkHtmlToPdfGenerator::class);
        $this->mockTranslator = m::mock(Translator::class);
        $this->mockLogger = m::mock(\Symfony\Bridge\Monolog\Logger::class);
        $this->mockCsvGenerator = m::mock(CsvGeneratorService::class);

        $this->mockReport = m::mock(ReportInterface::class);
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
        $mockContainer->shouldReceive('get')->with('mail_sender')->andReturn($this->mockMailSender);
        $mockContainer->shouldReceive('get')->with('mail_factory')->andReturn($this->mockMailFactory);
        $mockContainer->shouldReceive('get')->with('templating')->andReturn($this->mockTemplatingEngine);
        $mockContainer->shouldReceive('get')->with('wkhtmltopdf')->andReturn($this->mockPdfGenerator);
        $mockContainer->shouldReceive('get')->with('translator')->andReturn($this->mockTranslator);
        $mockContainer->shouldReceive('get')->with('logger')->andReturn($this->mockLogger);
        $mockContainer->shouldReceive('get')->with('csv_generator')->andReturn($this->mockCsvGenerator);

        return new ReportSubmissionService($mockContainer);
    }

    public function testGenerateReportDocuments()
    {
        $this->mockReport->shouldReceive('createAttachmentName')->with(m::type('String'))->andReturn('DigidepsFile');

        $this->mockTemplatingEngine->shouldReceive('render')
            ->with(
                'AppBundle:Report/Formatted:formatted_body.html.twig',
                [
                    'report' => $this->mockReport,
                    'showSummary' => true
                ]
            )
            ->andReturn('Report HTML');

        $this->mockPdfGenerator->shouldReceive('getPdfFromHtml')->with('Report HTML')->andReturn('PDF CONTENT');

        $this->mockFileUploader->shouldReceive('uploadFile')->with($this->mockReport, m::type('String'), m::type('String'), true);
        $this->mockCsvGenerator->shouldReceive('generateTransactionsCsv')->with($this->mockReport)->andReturn('CSV CONTENT');
        $this->mockFileUploader->shouldReceive('uploadFile')->with($this->mockReport, m::type('String'), m::type('String'), true);

        $this->sut = $this->generateSut();

        $this->sut->generateReportDocuments($this->mockReport);
    }

    public function testGetPdfBinaryContent()
    {
        $this->mockTemplatingEngine->shouldReceive('render')
            ->with(
                'AppBundle:Report/Formatted:formatted_body.html.twig',
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
            'report/' . $newYearReportId ,
            'Report\\Report'
        )->andReturn($newYearReport);

        $mockUser = m::mock(User::class);
        $mockUser->shouldReceive('isDeputyOrg')->once()->andReturn(true);

        $mockEmail  = m::mock(Email::class);
        $this->mockMailFactory->shouldReceive('createOrgReportSubmissionConfirmationEmail')
            ->once()
            ->with($mockUser, $this->mockReport, $newYearReport)
            ->andReturn($mockEmail);

        $this->mockMailSender->shouldReceive('send')
            ->once()
            ->with($mockEmail, ['text', 'html'], 'secure-smtp');

        $this->sut = $this->generateSut();

        $this->sut->submit($this->mockReport, $mockUser);
    }


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
            'report/' . $newYearReportId ,
            'Report\\Report'
        )->andReturn($newYearReport);

        $mockUser = m::mock(User::class);
        $mockUser->shouldReceive('isDeputyOrg')->once()->andReturn(false);

        $mockEmail  = m::mock(Email::class);
        $this->mockMailFactory->shouldReceive('createReportSubmissionConfirmationEmail')
            ->once()
            ->with($mockUser, $this->mockReport, $newYearReport)
            ->andReturn($mockEmail);

        $this->mockMailSender->shouldReceive('send')
            ->once()
            ->with($mockEmail, ['text', 'html']);

        $this->sut = $this->generateSut();

        $this->sut->submit($this->mockReport, $mockUser);
    }
}
