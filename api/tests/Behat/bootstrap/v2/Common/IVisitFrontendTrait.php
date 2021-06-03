<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

trait IVisitFrontendTrait
{
    /**
     * @When I visit the lay start page
     */
    public function iVisitLayStartPage()
    {
        $this->visitFrontendPath($this->getLayStartPageUrl());
    }

    /**
     * @When I visit the report overview page
     */
    public function iVisitReportOverviewPage()
    {
        $this->visitFrontendPath($this->getReportOverviewUrl($this->loggedInUserDetails->getCurrentReportId()));
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
        $this->visitFrontendPath($this->getMoneyOutShortSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the short money out summary section
     */
    public function iVisitMoneyOutShortSummarySection()
    {
        $this->visitFrontendPath($this->getMoneyOutShortSectionSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money out report section
     */
    public function iVisitMoneyOutSection()
    {
        $this->visitFrontendPath($this->getMoneyOutSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the money out summary section
     */
    public function iVisitMoneyOutSummarySection()
    {
        $this->visitFrontendPath($this->getMoneyOutSectionSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the accounts report section
     */
    public function iVisitAccountsSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'bank-accounts');
        $this->visitFrontendPath($reportSectionUrl);
    }

    /**
     * @When I visit the accounts summary section
     */
    public function iVisitAccountsSummarySection()
    {
        $this->visitFrontendPath($this->getAccountsSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the health and lifestyle report section
     */
    public function iVisitHealthAndLifestyleSection()
    {
        $this->visitFrontendPath($this->getHealthAndLifestyleSectionUrl($this->loggedInUserDetails->getCurrentReportId()));
    }

    /**
     * @When I visit the health and lifestyle summary section
     */
    public function iVisitHealthAndLifestyleSummarySection()
    {
        $this->visitFrontendPath($this->getHealthAndLifestyleSummaryUrl($this->loggedInUserDetails->getCurrentReportId()));
    }
}
