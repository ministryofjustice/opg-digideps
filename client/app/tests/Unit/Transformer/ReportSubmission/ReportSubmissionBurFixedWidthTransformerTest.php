<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Transformer\ReportSubmission;

use OPG\Digideps\Frontend\Entity\Report\ReportSubmissionSummary;
use OPG\Digideps\Frontend\Transformer\ReportSubmission\ReportSubmissionBurFixedWidthTransformer;
use PHPUnit\Framework\TestCase;

class ReportSubmissionBurFixedWidthTransformerTest extends TestCase
{
    private string $result;
    private array $formattedResult;

    private ReportSubmissionBurFixedWidthTransformer $sut;

    public function setUp(): void
    {
        $this->sut = new ReportSubmissionBurFixedWidthTransformer();
    }

    public function testTransformsACollectionOfReportSubmissionSummaryEntities(): void
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

    public function testIgnoresInvalidTypesInTheInput(): void
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

    private function buildAlphaReportSubmissionSummary(): ReportSubmissionSummary
    {
        return new ReportSubmissionSummary()
            ->setId(1)
            ->setCaseNumber('11111111')
            ->setDateReceived(new \DateTime('10-02-2001'))
            ->setFormType('ReportOne')
            ->setScanDate(new \DateTime('11-02-2001'))
            ->setDocumentType('Foo')
            ->setDocumentId('report_one.pdf');
    }

    private function buildBetaReportSubmissionSummary(): ReportSubmissionSummary
    {
        return new ReportSubmissionSummary()
            ->setId(2)
            ->setCaseNumber('22222222')
            ->setDateReceived(new \DateTime('20-02-2001'))
            ->setFormType('ReportTwo')
            ->setScanDate(new \DateTime('21-02-2001'))
            ->setDocumentType('Bar')
            ->setDocumentId('report_two.pdf');
    }

    private function invokeTransformer(array $input): void
    {
        $this->result = $this->sut->transform($input);
    }

    private function formatResultIntoTestable(): void
    {
        $this->formattedResult = explode("\r\n", $this->result);
        array_pop($this->formattedResult);
    }

    private function assertResultContainsHeaderLine(): void
    {
        self::assertEquals('00000000', $this->formattedResult[0]);
    }

    private function assertResultContainsNdataLines($expectedCount): void
    {
        self::assertCount($expectedCount + 2, $this->formattedResult);
    }

    private function assertEachDataLineIsFixedLength(): void
    {
        $result = $this->formattedResult;

        array_shift($result);
        array_pop($result);

        /** @var string $dataLine */
        foreach ($result as $dataLine) {
            self::assertEquals(375, strlen($dataLine));
            self::assertEquals(325, $this->determineNumFixedSpaces($dataLine));
        }
    }

    private function determineNumFixedSpaces(string $line): int
    {
        preg_match_all('/ /', $line, $matches);

        return count($matches[0]);
    }

    private function assertResultContainsFooterLine(): void
    {
        self::assertEquals('99999999', $this->formattedResult[count($this->formattedResult) - 1]);
    }
}
