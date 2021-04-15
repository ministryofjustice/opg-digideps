<?php declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

trait VisitsCareSectionTrait
{
    private int $answeredYes = 0;
    private int $answeredNo = 0;
    private array $additionalInfo = [];


    /**
     * @Given I view the visits and care report section
     */
    public function iViewVisitsCareSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $activeReportId, 'visits-care');
        $this->visitPath($reportSectionUrl);
    }

    /**
     * @Given I view and start the visits and care report section
     */
    public function iViewAndStartVisitsCareSection()
    {
        $this->iViewVisitsCareSection();
        $this->clickLink('Start visits and care');
    }

    /**
     * @Given I choose yes and save on the live with the client section
     */
    public function iChooseYesOnLiveWithTheClientSection()
    {
        $this->selectOption('visits_care[doYouLiveWithClient]', 'yes');
        $this->answeredYes += 1;
        $this->pressButton('Save and continue');
    }

    /**
     * @Given I choose no and save on the live with the client section
     */
    public function iChooseNoOnLiveWithTheClientSection()
    {
        $this->selectOption('visits_care[doYouLiveWithClient]', 'no');
        $this->answeredNo += 1;

        $info = 'The first set of information';
        $this->fillField('visits_care[howOftenDoYouContactClient]', $info);
        array_push($this->additionalInfo, $info);

        $this->pressButton('Save and continue');
    }

    /**
     * @Given I choose no and save on the client receive paid care section
     */
    public function iChooseNoOnReceivePaidCareSection()
    {
        $this->selectOption('visits_care[doesClientReceivePaidCare]', 'no');
        $this->answeredNo += 1;
        $this->pressButton('Save and continue');
    }

    /**
     * @Given I choose yes and save on the client receive paid care section
     */
    public function iChooseYesOnReceivePaidCareSection()
    {
        $this->selectOption('visits_care[doesClientReceivePaidCare]', 'yes');
        $this->answeredYes += 1;

        $this->selectOption('visits_care[howIsCareFunded]', 'client_pays_for_all');
        $this->pressButton('Save and continue');
    }

    /**
     * @Given I fill out and save the who is doing caring section
     */
    public function iFillOutWhoIsDoingCaringSection()
    {
        $info = 'The second set of information';
        $this->fillField('visits_care[whoIsDoingTheCaring]', $info);
        array_push($this->additionalInfo, $info);

        $this->pressButton('Save and continue');
    }

    /**
     * @Given I choose no and save on the client has care plan section
     */
    public function iChooseNoOnHasCarePlanSection()
    {
        $this->selectOption('visits_care[doesClientHaveACarePlan]', 'no');
        $this->answeredNo += 1;
        $this->pressButton('Save and continue');
    }

    /**
     * @Given I choose yes and save on the client has care plan section
     */
    public function iChooseYesOnHasCarePlanSection()
    {
        $this->selectOption('visits_care[doesClientHaveACarePlan]', 'yes');
        $this->answeredYes += 1;

        $monthNumber = '12';
        $monthName = 'December';
        $this->fillField('visits_care[whenWasCarePlanLastReviewed][month]', $monthNumber);
        array_push($this->additionalInfo, $monthName);

        $year = '2015';
        $this->fillField('visits_care[whenWasCarePlanLastReviewed][year]', $year);
        array_push($this->additionalInfo, $year);

        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see the expected visits and care report section responses
     */
    public function iSeeExpectedVisitCareSectionResponses()
    {
        $table = $this->getSession()->getPage()->find('css', 'dl');

        if (!$table) {
            $this->throwContextualException('A dl element was not found on the page');
        }

        $tableEntry = $table->findAll('css', 'dd');

        if (!$tableEntry) {
            $this->throwContextualException('A dd element was not found on the page');
        }

        $countNegativeResponse = 0;
        $countPositiveResponse = 0;

        foreach ($tableEntry as $entry) {
            if (trim(strtolower($entry->getHtml())) === "no") {
                $countNegativeResponse += 1;
            } elseif (strtolower(trim($entry->getHtml())) === "yes") {
                $countPositiveResponse += 1;
            }
        }

        assert($countNegativeResponse == $this->answeredNo);
        assert($countPositiveResponse == $this->answeredYes);
    }

    /**
     * @Then I should see all the additional information I gave for visit and care
     */
    public function iShouldSeeTheExpectedVisitCareAdditionalInfo()
    {
        $table = $this->getSession()->getPage()->find('css', 'dl');

        if (!$table) {
            $this->throwContextualException('A dl element was not found on the page');
        }

        foreach ($this->additionalInfo as $info) {
            assert(str_contains($table->getHtml(), $info));
        }
    }

    /**
     * @Then I follow edit link for does client receive paid care page
     */
    public function iFollowEditLinkClientReceivePaidCarePage()
    {
        //this should be replaced with actual link click but could not identify it properly
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportReceivePaidCare = 'report/' . $activeReportId . '/visits-care/step/2?from=summary';
        $this->visitPath($reportReceivePaidCare);
    }
}
