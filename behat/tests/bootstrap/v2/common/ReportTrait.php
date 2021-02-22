<?php declare(strict_types=1);

namespace DigidepsBehat\v2\Common;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Result\ExecutedStepResult;

trait ReportTrait
{
    private function logInAndEnterReport(): void
    {
        $this->iAmLoggedInAsWithPassword(self::$currentReportCache['deputy'] . '@behat-test.com', 'Abcd1234');
        $reportId = self::$currentReportCache['reportId'];
        $reportType = self::$currentReportCache['reportType'];
        $this->visit("$reportType/$reportId/overview");
    }

    private function completeReport(string $reportType)
    {
        $this->logInAndEnterReport();

        $sections = $this->getSession()->getPage()->findAll('xpath', "//a[contains(@id, 'edit-')]");
        $sectionNames = [];
        foreach ($sections as $section) {
            $sectionId = $section->getAttribute('id');
            $sectionNames[] = substr($sectionId, strpos($sectionId, "-") + 1);
        }

        if ($matches = array_keys($sectionNames, 'report-preview')) {
            foreach ($matches as $index) {
                unset($sectionNames[$index]);
            }
        }

        $this->completeSections(implode(',', $sectionNames), $reportType);
    }
}
