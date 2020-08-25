<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\Report\Satisfaction;
use AppBundle\Controller\Admin\StatsController;

class StatsControllerTest extends AbstractControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testCreateSatisfactionSpreadsheet(): void
    {
        $reportSatisfactionSummaries = [];

        $satisfactionSummary1 = new Satisfaction();
        $satisfactionSummary1->setId(1);
        $satisfactionSummary1->setScore(3);
        $satisfactionSummary1->setCreated("2020-08-04 00:00:00");
        $satisfactionSummary1->setComments("a comment");

        $satisfactionSummary2 = new Satisfaction();
        $satisfactionSummary2->setId(2);
        $satisfactionSummary2->setScore(4);
        $satisfactionSummary2->setCreated("2020-08-01 00:00:00");
        $satisfactionSummary2->setComments("another comment");

        array_push($reportSatisfactionSummaries, $satisfactionSummary1, $satisfactionSummary2);
        $spreadsheet = StatsController::createSatisfactionSpreadsheet($reportSatisfactionSummaries);

        self::assertEquals("Digideps", $spreadsheet->getProperties()->getCreator());
        self::assertEquals("ID", $spreadsheet->getActiveSheet()->getCell('A1'));
        self::assertEquals("Score", $spreadsheet->getActiveSheet()->getCell('B1'));
        self::assertEquals("Created Date", $spreadsheet->getActiveSheet()->getCell('C1'));
        self::assertEquals("Comments", $spreadsheet->getActiveSheet()->getCell('D1'));
        self::assertEquals("1", $spreadsheet->getActiveSheet()->getCell('A2'));
        self::assertEquals("3", $spreadsheet->getActiveSheet()->getCell('B2'));
        self::assertEquals("2020-08-04 00:00:00", $spreadsheet->getActiveSheet()->getCell('C2'));
        self::assertEquals("a comment", $spreadsheet->getActiveSheet()->getCell('D2'));
        self::assertEquals("2", $spreadsheet->getActiveSheet()->getCell('A3'));
        self::assertEquals("4", $spreadsheet->getActiveSheet()->getCell('B3'));
        self::assertEquals("2020-08-01 00:00:00", $spreadsheet->getActiveSheet()->getCell('C3'));
        self::assertEquals("another comment", $spreadsheet->getActiveSheet()->getCell('D3'));
        self::assertEquals("", $spreadsheet->getActiveSheet()->getCell('D4'));
        self::assertIsObject($spreadsheet);
    }

    public function testCreateSatisfactionSpreadsheetTypeError(): void
    {
        $reportSatisfactionSummaries = [];

        $satisfactionSummary = new Satisfaction();
        $satisfactionSummary->setId(2);
        $satisfactionSummary->setScore(4);

        array_push($reportSatisfactionSummaries, $satisfactionSummary);

        $this->expectException(\TypeError::class);
        StatsController::createSatisfactionSpreadsheet($reportSatisfactionSummaries);
    }
}
