<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

use Exception;

trait ReportTrait
{
    /**
     * @Given /^I submit the report$/
     */
    public function iSubmitTheReport()
    {
        $ndrOrReport = $this->layDeputyCompletedNotSubmittedDetails->getCurrentReportNdrOrReport();
        $reportId = $this->layDeputyCompletedNotSubmittedDetails->getCurrentReportId();

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
        ;
        $this->checkOption(sprintf('%s_declaration[agree]', $ndrOrReport));
        $this->selectOption(sprintf('%s_declaration[agreedBehalfDeputy]', $ndrOrReport), 'only_deputy');
        $this->pressButton(sprintf('%s_declaration[save]', $ndrOrReport));
    }

    /**
     * @Given a Lay Deputy completes and submits a report
     * @throws Exception
     */
    public function aLayDeputyCompletesAndSubmitsAReport()
    {
        $this->aLayDeputyHasCompletedReport();
        $this->iSubmitTheReport();
    }

    /**
     * @Given a Lay Deputy has a new report
     */
    public function aLayDeputyHasNewReport()
    {
        if (empty($this->layDeputyNotStartedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyNotStartedDetails');
        }

        $this->loginToFrontendAs($this->layDeputyNotStartedDetails->getEmail());
    }

    /**
     * @Given a Lay Deputy has a completed report
     * @throws Exception
     */
    public function aLayDeputyHasCompletedReport()
    {
        if (empty($this->layDeputyCompletedNotSubmittedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputyCompletedNotSubmittedDetails');
        }

        $this->loginToFrontendAs($this->layDeputyCompletedNotSubmittedDetails->getEmail());
    }

    /**
     * @Given a Lay Deputy has submitted a report
     * @throws Exception
     */
    public function aLayDeputyHasSubmittedAReport()
    {
        if (empty($this->layDeputySubmittedDetails)) {
            throw new Exception('It looks like fixtures are not loaded - missing $layDeputySubmittedDetails');
        }

        $this->loginToFrontendAs($this->layDeputySubmittedDetails->getEmail());
    }
}
