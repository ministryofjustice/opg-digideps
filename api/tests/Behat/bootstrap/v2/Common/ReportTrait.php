<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use Exception;

trait ReportTrait
{
    public string $reportUrlPrefix = 'report';

    /**
     * @Given /^I submit the report$/
     */
    public function iSubmitTheReport()
    {
        $ndrOrReport = $this->layDeputyCompletedDetails->getCurrentReportNdrOrReport();
        $reportId = $this->layDeputyCompletedDetails->getCurrentReportId();

        $this->visit("$ndrOrReport/$reportId/overview");

        try {
            $this->clickLink('Preview and check report');
        } catch (Exception $e) {
            // Convert once we start to look at NDRs
            $this->throwContextualException("Couldn't find link with text 'Preview and check report'");
//            $link = $reportType === 'ndr' ? 'edit-report-review' : 'edit-report_submit';
//            $this->clickOnBehatLink($link);
        }

        $this->clickLink('Continue');

        $this->checkOption(sprintf('%s_declaration[agree]', $ndrOrReport));
        $this->selectOption(sprintf('%s_declaration[agreedBehalfDeputy]', $ndrOrReport), 'only_deputy');
        $this->pressButton(sprintf('%s_declaration[save]', $ndrOrReport));
    }

    /**
     * @Given a Lay Deputy has not started a report
     * @Given a Lay Deputy has not started a Pfa High Assets report
     */
    public function aLayDeputyHasNotStartedAReport()
    {
        if (empty($this->layDeputyNotStartedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyNotStartedDetails');
        }

        $this->loginToFrontendAs($this->layDeputyNotStartedDetails->getUserEmail());
    }

    /**
     * @Given a Lay Deputy has not started a Pfa Low Assets report
     */
    public function aLayDeputyHasNotStartedAPfaLowAssetsReport()
    {
        if (empty($this->layDeputyNotStartedPfaLowAssetsDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyNotStartedPfaLowAssetsDetails');
        }

        $this->loginToFrontendAs($this->layDeputyNotStartedPfaLowAssetsDetails->getUserEmail());
    }

    /**
     * @Given a Lay Deputy has completed a Pfa Low Assets report
     */
    public function aLayDeputyHasCompletedAPfaLowAssetsReport()
    {
        if (empty($this->layDeputyCompletedPfaLowAssetsDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyCompletedPfaLowAssetsDetails');
        }

        $this->loginToFrontendAs($this->layDeputyCompletedPfaLowAssetsDetails->getUserEmail());
    }

    /**
     * @Given a Lay Deputy has a completed report
     * @Given a Lay Deputy has completed a Pfa High Assets report
     *
     * @throws Exception
     */
    public function aLayDeputyHasCompletedReport()
    {
        if (empty($this->layDeputyCompletedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyCompletedDetails');
        }

        $this->loginToFrontendAs($this->layDeputyCompletedDetails->getUserEmail());
    }

    /**
     * @Given a Lay Deputy has not started an NDR report
     */
    public function aNdrLayDeputyHasNotStartedAReport()
    {
        if (empty($this->ndrLayDeputyNotStartedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $ndrLayDeputyNotStartedDetails');
        }

        $this->loginToFrontendAs($this->ndrLayDeputyNotStartedDetails->getUserEmail());
        $this->reportUrlPrefix = $this->ndrLayDeputyNotStartedDetails->getCurrentReportNdrOrReport();
    }

    /**
     * @Given a Lay Deputy has a completed NDR report
     *
     * @throws Exception
     */
    public function aNdrLayDeputyHasCompletedReport()
    {
        if (empty($this->ndrLayDeputyCompletedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $ndrLayDeputyCompletedDetails');
        }

        $this->loginToFrontendAs($this->ndrLayDeputyCompletedDetails->getUserEmail());
        $this->reportUrlPrefix = $this->ndrLayDeputyCompletedDetails->getCurrentReportNdrOrReport();
    }

    /**
     * @Given a Lay Deputy has submitted a report
     *
     * @throws Exception
     */
    public function aLayDeputyHasSubmittedAReport()
    {
        if (empty($this->layDeputySubmittedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputySubmittedDetails');
        }

        $this->loginToFrontendAs($this->layDeputySubmittedDetails->getUserEmail());
    }

    /**
     * @Given a Professional Admin Deputy has not started a report
     */
    public function aProfessionalAdminDeputyHasNotStartedAReport()
    {
        if (empty($this->profAdminDeputyNotStartedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $profAdminDeputyNotStartedDetails');
        }

        $this->loginToFrontendAs($this->profAdminDeputyNotStartedDetails->getUserEmail());
    }
}
