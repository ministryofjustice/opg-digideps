<?php

namespace AppBundle\Transformer\ReportSubmission;

use AppBundle\Entity\Report\ReportSubmissionSummary;
use PHPUnit\Framework\TestCase;

class ReportSubmissionBurFixedWidthTransformerTest extends TestCase
{
    /** @var ReportSubmissionBurFixedWidthTransformer */
    private $sut;

    /** @var string */
    private $result;

    /** @var array */
    private $formattedResult;

    public function setUp()
    {
        $this->sut = new ReportSubmissionBurFixedWidthTransformer();
    }

    public function testTransformsACollectionOfReportSubmissionSummaryEntities()
    {
        $input = [
            $this->buildAlphaReportSubmissionSummary(),
            $this->buildBetaReportSubmissionSummary(),
        ];

        $this->invokeTransformer($input);
        $this->formatResultIntoTestable();

        $this->assertResultContainsHeaderLine();
        $this->assertResultContainsNdataLines(2);
        $this->assertEachDataLineIsFixedLength();
        $this->assertResultContainsFooterLine();
    }

    public function testIgnoresInvalidTypesInTheInput()
    {
        $input = [
            $this->buildAlphaReportSubmissionSummary(),
            [],
        ];

        $this->invokeTransformer($input);
        $this->formatResultIntoTestable();

        $this->assertResultContainsHeaderLine();
        $this->assertResultContainsNdataLines(1);
        $this->assertEachDataLineIsFixedLength();
        $this->assertResultContainsFooterLine();
    }

    /**
     * @return ReportSubmissionSummary
     */
    private function buildAlphaReportSubmissionSummary()
    {
        return (new ReportSubmissionSummary())
            ->setId(1)
            ->setCaseNumber('11111111')
            ->setDateReceived(new \DateTime('10-02-2001'))
            ->setFormType('ReportOne')
            ->setScanDate(new \DateTime('11-02-2001'))
            ->setDocumentType('Foo')
            ->setDocumentId('report_one.pdf');
    }

    /**
     * @return ReportSubmissionSummary
     */
    private function buildBetaReportSubmissionSummary()
    {
        return (new ReportSubmissionSummary())
            ->setId(2)
            ->setCaseNumber('22222222')
            ->setDateReceived(new \DateTime('20-02-2001'))
            ->setFormType('ReportTwo')
            ->setScanDate(new \DateTime('21-02-2001'))
            ->setDocumentType('Bar')
            ->setDocumentId('report_two.pdf');
    }

    /**
     * @param $input
     */
    private function invokeTransformer($input)
    {
        $this->result = $this->sut->transform($input);
    }

    private function formatResultIntoTestable()
    {
        $this->formattedResult = explode("\n", $this->result);
        array_pop($this->formattedResult);
    }

    private function assertResultContainsHeaderLine()
    {
        $this->assertEquals('00000000', $this->formattedResult[0]);
    }

    private function assertResultContainsNdataLines($expectedCount)
    {
        $this->assertCount($expectedCount + 2, $this->formattedResult);
    }

    private function assertEachDataLineIsFixedLength()
    {
        $result = $this->formattedResult;

        array_shift($result);
        array_pop($result);

        foreach ($result as $dataLine) {
            $this->assertEquals(375, strlen($dataLine));
            $this->assertEquals(325, $this->determineNumFixedSpaces($dataLine));
        }
    }

    /**
     * @param $line
     * @return int
     */
    private function determineNumFixedSpaces($line)
    {
        preg_match_all('/ /', $line, $matches);

        return count($matches[0]);
    }

    private function assertResultContainsFooterLine()
    {
        $this->assertEquals('99999999', $this->formattedResult[count($this->formattedResult) -1]);
    }
}
