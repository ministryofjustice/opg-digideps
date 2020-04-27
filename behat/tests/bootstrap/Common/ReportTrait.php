<?php

namespace DigidepsBehat\Common;

use Behat\Gherkin\Node\TableNode;

trait ReportTrait
{
    private static $reportsCache = [];
    private static $currentReportCache = [];
    protected $sections103 = ['Deputy expenses', 'Decisions', 'Contacts', 'Visits and care', 'Accounts', 'Gifts', 'Money in', 'Money out', 'Assets', 'Debts', 'Actions', 'Other information', 'Documents'];

    /**
     * @When I set the report end date to :endDateDMY
     */
    public function iSetTheReportEndDateToAndEndDateTo($endDateDMY)
    {
        /* $startDatePieces = explode('/', $startDateDMY);
          $this->fillField('report_startDate_day', $startDatePieces[0]);
          $this->fillField('report_startDate_month', $startDatePieces[1]);
          $this->fillField('report_startDate_year', $startDatePieces[2]); */

        $endDatePieces = explode('/', $endDateDMY);
        $this->fillField('report_endDate_day', $endDatePieces[0]);
        $this->fillField('report_endDate_month', $endDatePieces[1]);
        $this->fillField('report_endDate_year', $endDatePieces[2]);

        $this->pressButton('report_save');
        $this->theFormShouldBeValid();
        $this->assertResponseStatus(200);
    }

    /**
     * @When I set the report start date to :endDateDMY
     */
    public function iSetTheReportStartDateToAndEndDateTo($startDateDMY)
    {
        $startDatePieces = explode('/', $startDateDMY);
        $this->fillField('report_startDate_day', $startDatePieces[0]);
        $this->fillField('report_startDate_month', $startDatePieces[1]);
        $this->fillField('report_startDate_year', $startDatePieces[2]);
    }

    private function gotoOverview()
    {
        $this->clickOnBehatLink('breadcrumbs-report-overview');
    }

    /**
     * @When I save the report as :reportId
     */
    public function iSaveTheReportAs($reportId)
    {
        $url = $this->getSession()->getCurrentUrl();
        preg_match('/\/(ndr|report)\/(\d+)\//', $url, $match);
        self::$reportsCache[$reportId] = [
            'type' => $match[1],
            'id' => $match[2],
        ];
    }

    /**
     * @When I go to the report URL :url for :reportId
     */
    public function iGoToTheReportUrl($url, $reportId)
    {
        $report = self::$reportsCache[$reportId];
        $fullUrl = '/' . $report['type'] . '/' . $report['id'] . '/' . $url;

        $this->visitPath($fullUrl);
    }

    /**
     * @Then the following :reportId report pages should return the following status:
     */
    public function theFollowingReportPagesShouldReturnTheFollowingStatus($reportId, TableNode $table)
    {
        $report = self::$reportsCache[$reportId];

        foreach ($table->getRowsHash() as $url => $expectedReturnCode) {
            $fullUrl = '/' . $report['type'] . '/' . $report['id'] . '/' . $url;
            $this->visitPath($fullUrl);

            $actual = $this->getSession()->getStatusCode();
            if (intval($expectedReturnCode) !== intval($actual)) {
                throw new \RuntimeException("$fullUrl: Current response status code is $actual, but $expectedReturnCode expected.");
            }
        }
    }

    /**
     * @Given the :usertype report should not be submittable
     */
    public function theReportShouldNotBeSubmittable($usertype = 'lay')
    {
        $usertype = strtolower(trim($usertype));
        $this->assertUrlRegExp('#/overview#');
        if ($usertype == 'lay') {
            # Lay report
            $this->assertSession()->elementExists('css', '#edit-report-preview');
            $this->assertSession()->elementNotExists('css', '#edit-report-review');
        } elseif (in_array($usertype, ['pa', 'prof'])) {
            # PA
            $this->assertSession()->elementNotExists('css', '#edit-report_submit');
        } else {
            throw new \RuntimeException('usertype not specified. Usage: the PA|Lay report should not be submittable');
        }
    }

    /**
     * @Given the :usertype report should be submittable
     */
    public function theReportShouldBeSubmittable($usertype = 'lay')
    {
        $usertype = strtolower(trim($usertype));
        $this->assertUrlRegExp('#/overview#');
        if ($usertype == 'lay') {
            # Lay report
            $this->assertSession()->elementExists('css', '#edit-report-review');
            $this->assertSession()->elementNotExists('css', '#edit-report-preview');
        } elseif (in_array($usertype, ['pa', 'prof'])) {
            # PA
            $this->assertSession()->elementExists('css', '#edit-report_submit');
        } else {
            throw new \RuntimeException('usertype not specified. Usage: the PA|Lay report should be submittable');
        }
    }

    /**
     * @Given I have the :startDate to :endDate report between :deputy and :client
     */
    public function iHaveTheReportBetweenDeputyAndClient($startDate, $endDate, $deputy, $client)
    {
        $this->iAmLoggedInAsWithPassword($deputy.'@behat-test.com', 'Abcd1234');
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

    /**
     * @Given the :section section on the report has been completed
     */
    public function theSectionOnTheReportHasBeenCompleted($section)
    {
        $this->logInAndEnterReport();
        $this->completeSections($section);
    }

    /**
     * @Then the report should have the :type sections
     */
    public function theReportShouldHaveTheSections($type)
    {
        $this->logInAndEnterReport();

        foreach ($this->getSectionsByType($type) as $section) {
            $this->assertPageContainsText($section);
        }
    }

    private function getSectionsByType($type)
    {
        switch ($type) {
            case '103':
                return $this->sections103;
            case '103-5':
                $sections = $this->sections103;
                $sections[] = 'Deputy costs';
                $sections[] = 'Deputy costs estimate';
                unset($sections[0]); // 'Deputy expenses'
                return $sections;
            case '103-6':
                $sections = $this->sections103;
                $sections[] = 'Deputy fees and expenses';
                unset($sections[0]); // 'Deputy expenses'
                return $sections;
        }
    }

    /**
     * @Then the :section section on the report should be completed
     */
    public function theSectionOnTheReportShouldBeCompleted($section)
    {
        $this->logInAndEnterReport();
        $this->iShouldSeeTheBehatElement($section.'-state-done', 'region');
    }

    /**
     * @Then the report should be unsubmitted
     */
    public function theReportShouldBeUnsubmitted()
    {
        $this->iAmLoggedInToAdminAsWithPassword('casemanager@publicguardian.gov.uk', 'Abcd1234');

        $client = self::$currentReportCache['client'];
        $startDate = self::$currentReportCache['startDate'];
        $endDate = self::$currentReportCache['endDate'];

        $this->visitAdminPath("/admin/client/case-number/$client/details");
        $this->iShouldSeeTheRegionInTheRegion("report-$startDate-to-$endDate", 'report-group-incomplete');
    }

    /**
     * @Then the report URL ":url" for ":reportId" should not be accessible
     */
    public function theReportUrlForShouldNotBeAccessible($url, $reportId)
    {
        $report = self::$reportsCache[$reportId];
        $fullUrl = '/' . $report['type'] . '/' . $report['id'] . '/' . $url;

        $previousUrl = $this->getSession()->getCurrentUrl();
        $this->visit($fullUrl);
        $this->assertResponseStatusIn([403, 404, 500]);
        $this->visit($previousUrl);
    }

    /**
     * Check the response status was one of the provided codes
     */
    private function assertResponseStatusIn($codes)
    {
        $actualCode = $this->getSession()->getStatusCode();

        if (!in_array($actualCode, $codes)) {
            throw new \RuntimeException("Invalid status code: $actualCode");
        }
    }
    /**
     * @Given the report has been submitted
     */
    public function theReportHasBeenSubmitted()
    {
        $reportType = self::$currentReportCache['reportType'];

        if ($reportType === 'ndr') {
            $this->logInAndEnterReport();

            $this->completeSections(implode(',', ['visits_care', 'expenses', 'income_benefits', 'bank_accounts', 'assets', 'debts', 'actions', 'other_info']), $reportType);
        } else {
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

        $reportId = self::$currentReportCache['reportId'];
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

    private function completeSections(string $sections, string $reportType='report')
    {
        $this->iAmLoggedInToAdminAsWithPassword('admin@publicguardian.gov.uk', 'Abcd1234');

        $reportId = self::$currentReportCache['reportId'];
        $url = sprintf('/admin/fixtures/complete-sections/%s/%s?sections=%s', $reportType, $reportId, $sections);
        $this->visitAdminPath($url);
    }

    private function enterReport($client, $startDate, $endDate): void
    {
        if ($this->getSession()->getPage()->hasContent('Start now')) {
            $this->clickLink('Start now');
        } else if ($this->getSession()->getPage()->hasContent($startDate . ' to ' . $endDate . ' report')) {
            $this->clickLink($startDate . ' to ' . $endDate . ' report');
        } else {
            $this->clickLink($client.'-Client, John');
        }
    }

    private function logInAndEnterReport(): void
    {
        $this->iAmLoggedInAsWithPassword(self::$currentReportCache['deputy'] . '@behat-test.com', 'Abcd1234');
        $reportId = self::$currentReportCache['reportId'];
        $reportType = self::$currentReportCache['reportType'];
        $this->visit("$reportType/$reportId/overview");
    }
}
