<?php declare(strict_types=1);


namespace DigidepsBehat\v2\Common;

trait ReportTrait
{
    /**
     * @Given /^I submit the report$/
     */
    public function iSubmitTheReport()
    {
        $reportType = $this->layDeputyCompletedNotSubmittedDetails->getReportType();
        $reportId = $this->layDeputyCompletedNotSubmittedDetails->getCurrentReportId();

        $this->visit("$reportType/$reportId/overview");

        try {
            $this->clickLink('Preview and check report');
        } catch (\Exception $e) {
            $link = $reportType === 'ndr' ? 'edit-report-review' : 'edit-report_submit';
            $this->clickOnBehatLink($link);
        }

        $this->clickOnBehatLink($reportType === 'report' ? 'declaration-page' : 'ndr-declaration-page');
        $this->checkOption(sprintf('%s_declaration[agree]', $reportType));
        $this->selectOption(sprintf('%s_declaration[agreedBehalfDeputy]', $reportType), 'only_deputy');
        $this->pressButton(sprintf('%s_declaration[save]', $reportType));
    }

    private function enterReport(string $client, ?string $startDate = null, ?string $endDate = null): void
    {
        if ($this->getSession()->getPage()->hasContent('Start now')) {
            $this->clickLink('Start now');
        } elseif (($startDate && $endDate) && $this->getSession()->getPage()->hasContent($startDate . ' to ' . $endDate . ' report')) {
            $this->clickLink($startDate . ' to ' . $endDate . ' report');
        } elseif ($this->getSession()->getPage()->hasContent('Submitted reports')) {
            $this->clickLink('View');
        } else {
            try {
                $this->clickLink($client.'-Client, John');
            } catch (\Throwable $e) {
                $this->fillField('search', $client);
                $this->pressButton('search_submit');
                $this->clickLink($client.'-Client, John');
            }
        }
    }

    private function setCurrentReportDetails(string $deputyEmail)
    {
        $this->loginToFrontendAs($deputyEmail);
        $this->enterReport($client, $startDate, $endDate);
        preg_match('/\/(ndr|report)\/(\d+)\//', $this->getSession()->getCurrentUrl(), $match);

        self::$currentReportCache = [
            'deputy' => $deputy,
            'client' => $client,
            'reportId' => $match[2],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportType' => $match[1]
        ];
    }
}
