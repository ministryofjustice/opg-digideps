<?php

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Tests\Behat\BehatException;

trait ReportingSectionsTrait
{
    /**
     * @When I follow link back to report overview page
     */
    public function iNavigateBackToReportSection()
    {
        $this->iClickBasedOnAttributeTypeAndValue('a', 'data-action', 'report.overview');
        $this->iAmOnReportsOverviewPage();
    }

    /**
     * @When I press report sub section back button
     */
    public function iPressReportSubSectionBackButton()
    {
        $this->clickLink('Back');
    }

    /**
     * @When I view the report overview page
     */
    public function iGoToReportOverviewUrl()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportOverviewUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'overview');
        $this->visitPath($reportOverviewUrl);
    }

    /**
     * @When I view the NDR overview page
     */
    public function iGoToNDROverviewUrl()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportOverviewUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'overview');
        $this->visitPath($reportOverviewUrl);
    }

    /**
     * @When I should see :section as :status
     */
    public function iShouldSeeSectionAs($section, $status)
    {
        $this->iAmOnReportsOverviewPage();
        $sectionFormatted = sprintf('/%s/%s/%s', $this->reportUrlPrefix, $this->loggedInUserDetails->getCurrentReportId(), $section);
        $statusCorrect = false;
        $sectionExists = false;

        $reportSections = $this->getAllReportSections();

        foreach ($reportSections as $reportSection) {
            if ($reportSection->find('css', 'a')->getAttribute('href') === $sectionFormatted) {
                $sectionExists = true;
                $foundHtml = $reportSection->getHtml();
                $statuses = $reportSection->findAll('css', 'span');

                foreach ($statuses as $sts) {
                    if (trim(strtolower($sts->getHtml())) == $status) {
                        $statusCorrect = true;
                    }
                }
            }
        }

        if (!$sectionExists) {
            throw new BehatException(sprintf('href matching "%s" not found on %s.', $sectionFormatted, $this->getCurrentUrl()));
        }

        if (!$statusCorrect) {
            throw new BehatException(sprintf('Report section status not as expected. Status "%s" not found. Found: %s.', $status, $foundHtml));
        }
    }

    /**
     * @When I should not see :sectionName report section
     */
    public function iShouldNotSeeSection(string $sectionName)
    {
        $this->iAmOnReportsOverviewPage();
        $sectionFormatted = sprintf('/%s/%s/%s', $this->reportUrlPrefix, $this->loggedInUserDetails->getCurrentReportId(), $sectionName);
        $sectionExists = false;

        $reportSections = $this->getAllReportSections();

        foreach ($reportSections as $reportSection) {
            if ($reportSection->find('css', 'a')->getAttribute('href') === $sectionFormatted) {
                $sectionExists = true;
            }
        }

        if ($sectionExists) {
            throw new BehatException(sprintf('href matching "%s" was found on %s.', $sectionFormatted, $this->getCurrentUrl()));
        }
    }

    private function getAllReportSections()
    {
        $xpath = "//div[normalize-space(@class)='opg-overview-section']|//li[contains(@class, 'opg-overview-section')]";

        return $this->findAllXpathElements($xpath);
    }

    /**
     * @Then I should see text asking to answer the question
     */
    public function iSeeTextRequestingToAnswerQuestion()
    {
        $table = $this->getSession()->getPage()->find('css', 'dl');

        if (!$table) {
            throw new BehatException('A dl element was not found on the page');
        }

        $tableEntry = $table->findAll('css', 'dd');

        if (!$tableEntry) {
            throw new BehatException('A dd element was not found on the page');
        }

        $furtherInfoNeeded = false;

        foreach ($tableEntry as $entry) {
            if (str_contains(trim(strtolower($entry->getHtml())), 'please answer this question')) {
                $furtherInfoNeeded = true;
            }
        }

        assert(
            $furtherInfoNeeded,
            'The text: "please answer this question" not found'
        );
    }

    /**
     * @When I choose to save and add another
     */
    public function iChooseToSaveAndAddAnother()
    {
        $this->pressButton('Save and add another');
    }

    /**
     * @When I choose to save and continue
     */
    public function iChooseToSaveAndContinue()
    {
        $this->pressButton('Save and continue');
    }

    public function moneyFormat($value)
    {
        return number_format(floatval($value), 2, '.', ',');
    }
}
