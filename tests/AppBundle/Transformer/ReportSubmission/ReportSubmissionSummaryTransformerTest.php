<?php

namespace Tests\AppBundle\Transformer\ReportSummary;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use AppBundle\Service\DateTimeProvider;
use AppBundle\Transformer\ReportSubmission\ReportSubmissionSummaryTransformer;
use PHPUnit\Framework\TestCase;

class ReportSubmissionSummaryTransformerTest extends TestCase
{
    /** @var ReportSubmissionSummaryTransformer */
    private $sut;

    /** @var DateTimeProvider | \PHPUnit_Framework_MockObject_MockObject */
    private $dateTimeProvider;

    /** @var ReportSubmission | \PHPUnit_Framework_MockObject_MockObject */
    private $reportSubmission;

    /** @var array */
    private $result;

    public function setUp()
    {
        $this->dateTimeProvider = $this->getMock(DateTimeProvider::class);

        $this->sut = new ReportSubmissionSummaryTransformer($this->dateTimeProvider);

        $this->dateTimeProvider->method('getDateTime')->willReturn(new \DateTime('2013-01-01'));
    }

    public function testIgnoresRowsWithoutAreportOrNdr()
    {
        $this->ensureReportSubmissionIsMissingReportAndNdr();

        $this->result = $this->sut->transform([$this->reportSubmission]);

        $this->assertResultDoesNotContainDataRows();
    }

    private function ensureReportSubmissionIsMissingReportAndNdr()
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

    private function assertResultDoesNotContainDataRows()
    {
        $this->assertCount(1, $this->result);
    }

    public function testTransformsAReportSubmission()
    {
        $scanDate = new \DateTime('2013-01-01');
        $this->dateTimeProvider->method('getDateTime')->willReturn($scanDate);

        $ndrReportSubmission = $this->buildReportSubmissionWith([
            'report_type' => Ndr::class,
            'created_on' => new \DateTime('2012-01-01'),
            'report' => [
                'client' => ['case_number' => 132]
            ],
            'documents' => [
                ['filename' => 'NDR-report.pdf', 'is_report_pdf' => true]
            ]
        ]);

        $reportSubmission = $this->buildReportSubmissionWith([
            'report_type' => Report::class,
            'created_on' => new \DateTime('2012-01-02'),
            'report' => [
                'client' => ['case_number' => 133]
            ],
            'documents' => [
                ['filename' => 'full-report-transactions.pdf.csv', 'is_report_pdf' => true],
                ['filename' => 'full-report.pdf', 'is_report_pdf' => true],
                ['filename' => 'supporting-document.pdf', 'is_report_pdf' => false],
            ]
        ]);

        $expectedRows = [
            [
                'case_number' => 132,
                'date_received' => '01/01/2012',
                'scan_date' => '01/01/2013',
                'document_id' => 'NDR-report.pdf',
                'document_type' => 'Reports',
                'form_type' => 'Reports General'
            ],
            [
                'case_number' => 133,
                'date_received' => '02/01/2012',
                'scan_date' => '01/01/2013',
                'document_id' => 'full-report.pdf',
                'document_type' => 'Reports',
                'form_type' => 'Reports General'
            ],
        ];

        $this->result = $this->sut->transform([$ndrReportSubmission, $reportSubmission]);
        $this->assertResultContainsHeaderRow();
        $this->assertRowsContain($expectedRows);
    }

    public function testReturnsNullDocumentIdIfReportDocumentNotFound()
    {
        $scanDate = new \DateTime('2013-01-01');
        $this->dateTimeProvider->method('getDateTime')->willReturn($scanDate);

        $reportSubmission = $this->buildReportSubmissionWith([
            'report_type' => Report::class,
            'created_on' => new \DateTime('2012-01-01'),
            'report' => [
                'client' => ['case_number' => 132]
            ],
            'documents' => [
                ['filename' => 'full-report.pdf.csv', 'is_report_pdf' => true]
            ]
        ]);

        $expectedRows = [
            [
                'case_number' => 132,
                'date_received' => '01/01/2012',
                'scan_date' => '01/01/2013',
                'document_id' => null,
                'document_type' => 'Reports',
                'form_type' => 'Reports General'
            ]
        ];

        $this->result = $this->sut->transform([$reportSubmission]);
        $this->assertRowsContain($expectedRows);
    }

    /**
     * @param array $data
     * @return ReportSubmission
     */
    private function buildReportSubmissionWith(array $data)
    {
        $report = $this->buildReportOfType($data['report_type'], $data['report']);
        $reportSubmission = new ReportSubmission($report, new User());

        foreach ($data['documents'] as $document) {
            $reportSubmission->addDocument($this->buildDocumentWith($document));
        }

        $reportSubmission->setCreatedOn($data['created_on']);

        return $reportSubmission;
    }

    /**
     * @param $type
     * @param array $data
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildReportOfType($type, array $data)
    {
        $report = $this
            ->getMockBuilder($type)
            ->disableOriginalConstructor()
            ->getMock();

        $client = (new Client())->setCaseNumber($data['client']['case_number']);
        $report->method('getClient')->willReturn($client);

        return $report;
    }

    /**
     * @param $document
     * @return Document
     */
    private function buildDocumentWith($document)
    {
        $report = $this->getMockBuilder(Report::class)->disableOriginalConstructor()->getMock();

        return (new Document($report))
            ->setFileName($document['filename'])
            ->setIsReportPdf($document['is_report_pdf']);
    }

    private function assertResultContainsHeaderRow()
    {
        $this->assertEquals($this->result[0][0], 'case_number');
        $this->assertEquals($this->result[0][1], 'date_received');
        $this->assertEquals($this->result[0][2], 'scan_date');
        $this->assertEquals($this->result[0][3], 'document_id');
        $this->assertEquals($this->result[0][4], 'document_type');
        $this->assertEquals($this->result[0][5], 'form_type');
    }

    /**
     * @param array $expectedRows
     */
    private function assertRowsContain(array $expectedRows)
    {
        foreach ($expectedRows as $index => $expectedRow) {
            $this->assertResultContainsRow($expectedRow, $index + 1);
        }
    }

    /**
     * @param $expectedRow
     * @param $row
     */
    private function assertResultContainsRow($expectedRow, $row)
    {
        $this->assertEquals($expectedRow['case_number'], $this->result[$row][0]);
        $this->assertEquals($expectedRow['date_received'], $this->result[$row][1]);
        $this->assertEquals($expectedRow['scan_date'], $this->result[$row][2]);
        $this->assertEquals($expectedRow['document_id'], $this->result[$row][3]);
        $this->assertEquals($expectedRow['document_type'], $this->result[$row][4]);
        $this->assertEquals($expectedRow['form_type'], $this->result[$row][5]);
    }
}
