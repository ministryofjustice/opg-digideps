<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Report\Checklist;
use App\Entity\Report\Report;
use App\Entity\Report\ReviewChecklist;
use App\Exception\PdfGenerationFailedException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Twig\Environment;

class ChecklistPdfGeneratorTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $templating;
    private $htmltopdf;
    private $logger;

    /** @var ChecklistPdfGenerator */
    private $sut;

    public function setUp(): void
    {
        $this->templating = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $this->htmltopdf = $this->getMockBuilder(HtmlToPdfGenerator::class)->disableOriginalConstructor()->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();

        $this->sut = new ChecklistPdfGenerator($this->templating, $this->htmltopdf, $this->logger);
    }

    /**
     * @test
     */
    public function rendersHtmlAndConvertsToPdf()
    {
        $report = $this->buildReportInput();

        $this
            ->ensureHtmlRenderWillSucceed($report)
            ->ensurePdfGenerationWillSucceed();

        $result = $this->sut->generate($report);
        $this->assertEquals('pdf-content', $result);
    }

    /**
     * @test
     */
    public function throwsExceptionOnHtmlRenderError()
    {
        $report = $this->buildReportInput();
        $expectedException = new PdfGenerationFailedException('Failed to render HTML');

        $this
            ->ensureHtmlRenderWillFail($report)
            ->expectExceptionObject($expectedException);

        $this->sut->generate($report);
    }

    /**
     * @test
     */
    public function throwsExceptionOnHtmlToPdfError()
    {
        $report = $this->buildReportInput();
        $expectedException = new PdfGenerationFailedException('Unable to generate PDF using htmltopdf service');

        $this
            ->ensureHtmlRenderWillSucceed($report)
            ->ensurePdfGenerationWillFail()
            ->expectExceptionObject($expectedException);

        $this->sut->generate($report);
    }

    private function buildReportInput(): Report
    {
        $report = new Report();
        $checklist = new Checklist($report);
        $reviewChecklist = new ReviewChecklist($report);
        $report->setChecklist($checklist);
        $report->setReviewChecklist($reviewChecklist);

        return $report;
    }

    private function ensureHtmlRenderWillSucceed(Report $report): ChecklistPdfGeneratorTest
    {
        $this
            ->templating
            ->expects($this->once())
            ->method('render')
            ->with(ChecklistPdfGenerator::TEMPLATE_FILE, [
                'report' => $report,
                'lodgingChecklist' => $report->getChecklist(),
                'reviewChecklist' => $report->getReviewChecklist(),
            ])
            ->willReturn('some-html');

        return $this;
    }

    private function ensureHtmlRenderWillFail(Report $report): ChecklistPdfGeneratorTest
    {
        $this
            ->templating
            ->expects($this->once())
            ->method('render')
            ->with(ChecklistPdfGenerator::TEMPLATE_FILE, [
                'report' => $report,
                'lodgingChecklist' => $report->getChecklist(),
                'reviewChecklist' => $report->getReviewChecklist(),
            ])
            ->willThrowException(new \Exception('Failed to render HTML'));

        return $this;
    }

    private function ensurePdfGenerationWillSucceed(): void
    {
        $this
            ->htmltopdf
            ->expects($this->once())
            ->method('getPdfFromHtml')
            ->with('some-html')
            ->willReturn('pdf-content');
    }

    private function ensurePdfGenerationWillFail(): ChecklistPdfGeneratorTest
    {
        $this
            ->htmltopdf
            ->expects($this->once())
            ->method('getPdfFromHtml')
            ->with('some-html')
            ->willReturn(false);

        return $this;
    }
}
