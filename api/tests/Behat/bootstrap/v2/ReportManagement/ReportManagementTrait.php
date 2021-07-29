<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\ReportManagement;

use DateTime;

trait ReportManagementTrait
{
    /**
     * @When I manage the deputies :reportStatus report
     */
    public function iManageTheDeputiesSubmittedReport(string $reportStatus)
    {
        $this->iAmOnAdminClientDetailsPage();

        $reportId = 'completed' === $reportStatus ? $this->interactingWithUserDetails->getCurrentReportId() : $this->interactingWithUserDetails->getPreviousReportId();

        $xpathLocator = sprintf(
            "//a[contains(@href,'/admin/report/%s/manage')]",
            $reportId
        );

        $reportLink = $this->getSession()->getPage()->find('xpath', $xpathLocator);
        $reportLink->click();
    }

    /**
     * @When I change the report type to :reportType
     */
    public function iChangeReportTypeTo(string $reportType)
    {
        $this->iAmOnAdminManageReportPage();

        $this->chooseOption('manage_report[type]', '104-5', 'manage-report');
    }

    /**
     * @When I change the report due date to :numberOfWeeks weeks from now
     */
    public function iChangeReportDueDateToWeeks(string $numberOfWeeks)
    {
        $this->iAmOnAdminManageReportPage();

        $this->fillInField('manage_report[dueDateChoice]', '3', 'manage-report');
    }

    /**
     * @When I submit the new report details
     */
    public function iSubmitNewReportDetails()
    {
        $this->iAmOnAdminManageReportPage();
        $this->pressButton('Continue');

        $this->iAmOnAdminManageReportConfirmPage();
        $this->pressButton('Confirm');
    }

    /**
     * @Then the report details should be updated
     */
    public function reportDetailsShouldBeUpdated()
    {
        $reportPeriod = $this->interactingWithUserDetails->getCurrentReportPeriod();

        $locator = sprintf(
            "//td[normalize-space()='%s']/..",
            $reportPeriod
        );

        $reportRow = $this->getSession()->getPage()->find('xpath', $locator);

        $expectedReportType = $this->getSectionAnswers('manage-report')[0]['manage_report[type]'];

        $this->assertStringContainsString(
            $expectedReportType,
            $reportRow->getHtml(),
            'Comparing form answers against report row of client details page'
        );

        $numberWeeksExtended = $this->getSectionAnswers('manage-report')[0]['manage_report[dueDateChoice]'];
        $expectedDueDate = (new DateTime())
            ->modify(
                sprintf('+ %s weeks', $numberWeeksExtended)
            )
            ->format('j F Y');

        $this->assertStringContainsString(
            $expectedDueDate,
            $reportRow->getHtml(),
            'Comparing form answers against report row of client details page'
        );
    }
}
