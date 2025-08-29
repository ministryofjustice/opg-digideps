<?php

declare(strict_types=1);

namespace App\Tests\Unit\Transformer\ReportSubmission;

use PHPUnit\Framework\MockObject\MockObject;
use DateTime;
use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\User;
use App\Service\DateTimeProvider;
use App\Transformer\ReportSubmission\ReportSubmissionSummaryTransformer;
use PHPUnit\Framework\TestCase;

final class ReportSubmissionSummaryTransformerTest extends TestCase
{
    private ReportSubmissionSummaryTransformer $sut;
    private DateTimeProvider&MockObject $dateTimeProvider;
    private ReportSubmission&MockObject $reportSubmission;
    private array $result;

    public function setUp(): void
    {
        $this->dateTimeProvider = $this->createMock(DateTimeProvider::class);

        $this->sut = new ReportSubmissionSummaryTransformer($this->dateTimeProvider);

        $this->dateTimeProvider->method('getDateTime')->willReturn(new DateTime('2013-01-01'));
    }

    public function testIgnoresRowsWithoutAreportOrNdr(): void
    {
        $this->ensureReportSubmissionIsMissingReportAndNdr();

        $this->result = $this->sut->transform([$this->reportSubmission]);

        $this->assertResultDoesNotContainDataRows();
    }

    private function ensureReportSubmissionIsMissingReportAndNdr(): void
    {
        $this->reportSubmission = $this
            ->getMockBuilder(ReportSubmission::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->reportSubmission
            ->method('getReport')
            ->willReturn(null);

        $this
            ->reportSubmission
            ->method('getNdr')
            ->willReturn(null);
    }

    private function assertResultDoesNotContainDataRows(): void
    {
        $this->assertCount(0, $this->result);
    }

    public function testTransformsAReportSubmission(): void
    {
        $scanDate = new DateTime('2013-01-01');
        $this->dateTimeProvider->method('getDateTime')->willReturn($scanDate);

        $ndrReportSubmission = $this->buildReportSubmissionWith([
            'id' => 1,
            'report_type' => Ndr::class,
            'created_on' => new DateTime('2012-01-01'),
            'report' => [
                'client' => ['case_number' => '132'],
            ],
            'documents' => [
                ['filename' => 'NDR-report.pdf', 'is_report_pdf' => true],
            ],
        ]);

        $reportSubmission = $this->buildReportSubmissionWith([
            'id' => 2,
            'report_type' => Report::class,
            'created_on' => new DateTime('2012-01-02'),
            'report' => [
                'client' => ['case_number' => '133'],
            ],
            'documents' => [
                ['filename' => 'full-report-transactions.pdf.csv', 'is_report_pdf' => true],
                ['filename' => 'full-report.pdf', 'is_report_pdf' => true],
                ['filename' => 'supporting-document.pdf', 'is_report_pdf' => false],
            ],
        ]);

        $expectedRows = [
            [
                'id' => 1,
                'case_number' => 132,
                'date_received' => '2012-01-01',
                'scan_date' => '2013-01-01',
                'document_id' => 'NDR-report.pdf',
                'document_type' => 'Reports',
                'form_type' => 'Reports General',
            ],
            [
                'id' => 2,
                'case_number' => 133,
                'date_received' => '2012-01-02',
                'scan_date' => '2013-01-01',
                'document_id' => 'full-report.pdf',
                'document_type' => 'Reports',
                'form_type' => 'Reports General',
            ],
        ];

        $this->result = $this->sut->transform([$ndrReportSubmission, $reportSubmission]);
        $this->assertRowsContain($expectedRows);
    }

    public function testReturnsNullDocumentIdIfReportDocumentNotFound(): void
    {
        $scanDate = new DateTime('2013-01-01');
        $this->dateTimeProvider->method('getDateTime')->willReturn($scanDate);

        $reportSubmission = $this->buildReportSubmissionWith([
            'id' => 3,
            'report_type' => Report::class,
            'created_on' => new DateTime('2012-01-01'),
            'report' => [
                'client' => ['case_number' => '132'],
            ],
            'documents' => [
                ['filename' => 'full-report.pdf.csv', 'is_report_pdf' => true],
            ],
        ]);

        $expectedRows = [
            [
                'id' => 3,
                'case_number' => 132,
                'date_received' => '2012-01-01',
                'scan_date' => '2013-01-01',
                'document_id' => null,
                'document_type' => 'Reports',
                'form_type' => 'Reports General',
            ],
        ];

        $this->result = $this->sut->transform([$reportSubmission]);
        $this->assertRowsContain($expectedRows);
    }

    private function buildReportSubmissionWith(array $data): ReportSubmission
    {
        $report = $this->buildReportOfType($data['report_type'], $data['report']);
        $reportSubmission = new ReportSubmission($report, new User());

        foreach ($data['documents'] as $document) {
            $reportSubmission->addDocument($this->buildDocumentWith($document));
        }

        $reportSubmission->setId($data['id']);
        $reportSubmission->setCreatedOn($data['created_on']);

        return $reportSubmission;
    }

    private function buildReportOfType(string $type, array $data): MockObject
    {
        $report = $this
            ->getMockBuilder($type)
            ->disableOriginalConstructor()
            ->getMock();

        $client = (new Client())->setCaseNumber($data['client']['case_number']);
        $report->method('getClient')->willReturn($client);

        return $report;
    }

    private function buildDocumentWith(array $document): Document
    {
        $report = $this->getMockBuilder(Report::class)->disableOriginalConstructor()->getMock();

        return (new Document($report))
            ->setFileName($document['filename'])
            ->setIsReportPdf($document['is_report_pdf']);
    }

    private function assertRowsContain(array $expectedRows): void
    {
        foreach ($expectedRows as $index => $expectedRow) {
            $this->assertResultContainsRow($expectedRow, $index);
        }
    }

    private function assertResultContainsRow(array $expectedRow, int|string $row): void
    {
        $this->assertEquals($expectedRow['id'], $this->result[$row]['id']);
        $this->assertEquals($expectedRow['case_number'], $this->result[$row]['case_number']);
        $this->assertEquals($expectedRow['date_received'], $this->result[$row]['date_received']);
        $this->assertEquals($expectedRow['scan_date'], $this->result[$row]['scan_date']);
        $this->assertEquals($expectedRow['document_id'], $this->result[$row]['document_id']);
        $this->assertEquals($expectedRow['document_type'], $this->result[$row]['document_type']);
        $this->assertEquals($expectedRow['form_type'], $this->result[$row]['form_type']);
    }
}
