<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait IVisitFrontendTrait
{
    /**
     * @When I visit the report overview page
     */
    public function iViewReportOverviewPage()
    {
        $this->visitPath($this->getReportOverviewUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the report submitted page
     */
    public function iVisitReportSubmissionPage()
    {
        if (is_null($this->loggedInUserDetails->getPreviousReportId())) {
            $this->throwContextualException(
                "Logged in user doesn't have a previous report ID associated with them. Try using a user that has submitted a report instead."
            );
        }

        $submittedReportUrl = $this->getReportSubmittedUrl($this->loggedInUserDetails->getPreviousReportId());
        $this->visitFrontendPath($submittedReportUrl);
    }

    /**
     * @When I visit the short money out report section
     */
    public function iVisitMoneyOutShortSection()
    {
        $this->visitPath($this->getMoneyOutShortSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the short money out summary section
     */
    public function iVisitMoneyOutShortSummarySection()
    {
        $this->visitPath($this->getMoneyOutShortSectionSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money out report section
     */
    public function iVisitMoneyOutSection()
    {
        $this->visitPath($this->getMoneyOutSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money out summary section
     */
    public function iVisitMoneyOutSummarySection()
    {
        $this->visitPath($this->getMoneyOutSectionSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the accounts report section
     */
    public function iViewAccountsSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'bank-accounts');
        $this->visitPath($reportSectionUrl);
    }

    /**
     * @When I visit the accounts summary section
     */
    public function iViewAccountsSummarySection()
    {
        $this->visitPath($this->getAccountsSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the health and lifestyle report section
     */
    public function iVisitHealthAndLifestyleSection()
    {
        $this->visitPath($this->getHealthAndLifestyleSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the health and lifestyle summary section
     */
    public function iVisitHealthAndLifestyleSummarySection()
    {
        $this->visitPath($this->getHealthAndLifestyleSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }
}
