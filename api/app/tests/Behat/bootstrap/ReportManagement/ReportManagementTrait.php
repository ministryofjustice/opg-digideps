<?php

namespace App\Tests\Behat\ReportManagement;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ElementNotFoundException;

trait ReportManagementTrait
{
    /**
     * @When a case manager changes the report type on the active report to :type
     */
    public function aCaseManagerChangesTheReportTypeOnTheActiveReportTo($type)
    {
        // Remove report type suffix if there is one.
        if (false !== strpos($type, '-5') || false !== strpos($type, '-6')) {
            $type = substr($type, 0, -2);
        }

        $reportId = self::$currentReportCache['reportId'];

        $this->iAmLoggedInToAdminAsWithPassword('casemanager@publicguardian.gov.uk', 'DigidepsPass1234');
        $this->visitAdminPath("/admin/report/$reportId/manage");
        $this->selectOption('manage_report[type]', $type);
        $this->selectOption('manage_report[dueDateChoice]', 'keep');
        $this->pressButton('manage_report[save]');
        $this->pressButton('manage_report_confirm[save]');
        $this->assertPageContainsText('OPG'.$type);
    }

    /**
     * @When a case manager changes the report type on the submitted report to :type
     */
    public function aCaseManagerChangesTheReportTypeOnTheSubmittedReportTo($type)
    {
        // Remove report type suffix if there is one.
        if (false !== strpos($type, '-5') || false !== strpos($type, '-6')) {
            $type = substr($type, 0, -2);
        }

        $reportId = self::$currentReportCache['reportId'];

        $this->iAmLoggedInToAdminAsWithPassword('casemanager@publicguardian.gov.uk', 'DigidepsPass1234');
        $this->visitAdminPath("/admin/report/$reportId/manage");
        $this->selectOption('manage_report[type]', $type);
        $this->selectOption('manage_report[dueDateChoice]', 'keep');
        $this->checkOption('Any other information');
        $this->pressButton('manage_report[save]');
        $this->selectOption('manage_report_confirm[confirm]', 'yes');
        $this->pressButton('manage_report_confirm[save]');

        $this->assertPageContainsText('OPG'.$type);
    }

    /**
     * @When a case manager makes the following changes to the report:
     */
    public function aCaseManagerMakesTheFollowingChangesToTheReport(TableNode $table)
    {
        $this->aCaseManagerProposesToMakeTheFollowingChangesToTheReport($table);

        try {
            $this->selectOption('manage_report_confirm[confirm]', 'yes');
        } catch (ElementNotFoundException $e) {
        }

        $this->pressButton('manage_report_confirm[save]');
    }

    /**
     * @When a case manager proposes to make the following changes to the report:
     */
    public function aCaseManagerProposesToMakeTheFollowingChangesToTheReport(TableNode $table)
    {
        $reportId = self::$currentReportCache['reportId'];

        // Only log in and reload the session if we're not already on the management page.
        $currentUrl = $this->getSession()->getCurrentUrl();
        if (false === strpos($currentUrl, "/admin/report/$reportId/manage")) {
            $this->iAmLoggedInToAdminAsWithPassword('casemanager@publicguardian.gov.uk', 'DigidepsPass1234');
            $this->visitAdminPath("/admin/report/$reportId/manage");
        }

        foreach ($table as $inputs) {
            if (isset($inputs['type'])) {
                $this->selectOption('manage_report[type]', $inputs['type']);
            }

            if (isset($inputs['dueDateChoice'])) {
                $value = (intval($inputs['dueDateChoice'])) ?: $inputs['dueDateChoice'];
                $this->selectOption('manage_report[dueDateChoice]', $value);
            }

            if (isset($inputs['incompleteSection'])) {
                $this->checkOption($inputs['incompleteSection']);
            }

            break; // Only expect one row in this table.
        }

        $this->pressButton('manage_report[save]');
    }

    /**
     * @Given a case manager changes the due date on the report to :adjustment weeks later
     */
    public function aCaseManagerChangesTheDueDateOnTheReportToAdjustment($adjustment)
    {
        $adjustment = intval($adjustment);
        $reportId = self::$currentReportCache['reportId'];

        $this->iAmLoggedInToAdminAsWithPassword('casemanager@publicguardian.gov.uk', 'DigidepsPass1234');
        $this->visitAdminPath("/admin/report/$reportId/manage");
        $this->selectOption('manage_report[dueDateChoice]', $adjustment);
        $this->pressButton('manage_report[save]');
        $this->pressButton('manage_report_confirm[save]');
    }

    /**
     * @Given a case manager changes the due date on the report to :adjustment
     */
    public function aCaseManagerChangesTheDueDateOnTheReportToExact($adjustment)
    {
        $reportId = self::$currentReportCache['reportId'];
        $adjustment = explode('-', $adjustment);

        $this->iAmLoggedInToAdminAsWithPassword('casemanager@publicguardian.gov.uk', 'DigidepsPass1234');
        $this->visitAdminPath("/admin/report/$reportId/manage");
        $this->selectOption('manage_report[dueDateChoice]', 'custom');
        $this->fillField('manage_report_dueDateCustom_day', $adjustment[2]);
        $this->fillField('manage_report_dueDateCustom_month', $adjustment[1]);
        $this->fillField('manage_report_dueDateCustom_year', $adjustment[0]);
        $this->pressButton('manage_report[save]');
        $this->pressButton('manage_report_confirm[save]');
    }

    /**
     * @Then the due date on the report should be :adjustment weeks from now
     */
    public function theDueDateOnTheReportShouldBeWeeksFromNow($adjustment)
    {
        $client = self::$currentReportCache['client'];
        $startDate = self::$currentReportCache['startDate'];
        $endDate = self::$currentReportCache['endDate'];

        $this->iAmLoggedInToAdminAsWithPassword('casemanager@publicguardian.gov.uk', 'DigidepsPass1234');
        $this->visitAdminPath("/admin/client/case-number/$client/details");

        $adjustment = intval($adjustment);
        $expectedDueDate = (new \DateTime())->modify("+$adjustment weeks");
        $this->iShouldSeeInTheRegion($expectedDueDate->format('j F Y'), "report-$startDate-to-$endDate-due-date");
    }

    /**
     * @Then the due date on the report should be :adjustment
     */
    public function theDueDateOnTheReportShouldBe($adjustment)
    {
        $client = self::$currentReportCache['client'];
        $startDate = self::$currentReportCache['startDate'];
        $endDate = self::$currentReportCache['endDate'];

        $this->iAmLoggedInToAdminAsWithPassword('casemanager@publicguardian.gov.uk', 'DigidepsPass1234');
        $this->visitAdminPath("/admin/client/case-number/$client/details");

        $expectedDueDate = new \DateTime($adjustment);
        $this->iShouldSeeInTheRegion($expectedDueDate->format('j F Y'), "report-$startDate-to-$endDate-due-date");
    }

    /**
     * @Given I visit the management page of the :startDate to :endDate report between :deputy and :client
     */
    public function iVisitTheManagementPageOfTheToReportBetweenAnd($startDate, $endDate, $deputy, $client)
    {
        $this->iHaveTheReportBetweenDeputyAndClient($startDate, $endDate, $deputy, $client);
        $this->iVisitTheManagementPageOfTheReport();
    }

    /**
     * @Given I visit the management page of the report
     */
    public function iVisitTheManagementPageOfTheReport()
    {
        $reportId = self::$currentReportCache['reportId'];

        $this->iAmLoggedInToAdminAsWithPassword('casemanager@publicguardian.gov.uk', 'DigidepsPass1234');
        $this->visitAdminPath("/admin/report/$reportId/manage");
    }

    /**
     * @Given a case manager closes the report
     */
    public function aCaseManagerClosesTheReport()
    {
        $reportId = self::$currentReportCache['reportId'];

        $this->iAmLoggedInToAdminAsWithPassword('casemanager@publicguardian.gov.uk', 'DigidepsPass1234');
        $this->visitAdminPath("/admin/report/$reportId/manage");
        $this->checkOption('manage_report_close[agreeCloseReport]');
        $this->pressButton('manage_report_close[save]');
        $this->pressButton('close_report_confirm[save]');
    }
}
