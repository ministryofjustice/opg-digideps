<?php

declare(strict_types=1);

namespace DigidepsBehat\v2\Reporting\Sections;

trait VisitsCareSectionTrait
{
    private int $answeredYes = 0;
    private int $answeredNo = 0;
    private int $careFundedChoice = 0;
    private array $additionalInfo = [];

    /**
     * @Given I view the visits and care report section
     */
    public function iViewVisitsCareSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'visits-care');
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
        ++$this->answeredYes;
        $this->pressButton('Save and continue');
    }

    /**
     * @Given I choose no and save on the live with the client section
     */
    public function iChooseNoOnLiveWithTheClientSection()
    {
        $this->selectOption('visits_care[doYouLiveWithClient]', 'no');
        ++$this->answeredNo;

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
        ++$this->answeredNo;
        $this->pressButton('Save and continue');
    }

    /**
     * @Given I choose yes and save on the client receive paid care section
     */
    public function iChooseYesOnReceivePaidCareSection()
    {
        $this->selectOption('visits_care[doesClientReceivePaidCare]', 'yes');
        ++$this->answeredYes;

        $this->selectOption('visits_care[howIsCareFunded]', 'client_pays_for_all');
        $this->careFundedChoice = 1;
        $this->pressButton('Save and continue');
    }

    /**
     * @Given I choose yes and client pays for all care and then save on the receive paid care section
     */
    public function iChooseYesAndOptionOneOnReceivePaidCareSection()
    {
        $this->selectOption('visits_care[doesClientReceivePaidCare]', 'yes');
        ++$this->answeredYes;

        $this->selectOption('visits_care[howIsCareFunded]', 'client_pays_for_all');
        $this->careFundedChoice = 1;
        $this->pressButton('Save and continue');
    }

    /**
     * @Given I choose yes and client gets some financial help and then save on the receive paid care section
     */
    public function iChooseYesAndOptionTwoOnReceivePaidCareSection()
    {
        $this->selectOption('visits_care[doesClientReceivePaidCare]', 'yes');
        ++$this->answeredYes;

        $this->selectOption('visits_care[howIsCareFunded]', 'client_pays_for_all');
        $this->careFundedChoice = 2;
        $this->pressButton('Save and continue');
    }

    /**
     * @Given I choose yes and all care is paid for by someone else and then save on the receive paid care section
     */
    public function iChooseYesAndOptionThreeOnReceivePaidCareSection()
    {
        $this->selectOption('visits_care[doesClientReceivePaidCare]', 'yes');
        ++$this->answeredYes;

        $this->selectOption('visits_care[howIsCareFunded]', 'client_pays_for_all');
        $this->careFundedChoice = 3;
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
        ++$this->answeredNo;
        $this->pressButton('Save and continue');
    }

    /**
     * @Given I choose yes and save on the client has care plan section
     */
    public function iChooseYesOnHasCarePlanSection()
    {
        $this->selectOption('visits_care[doesClientHaveACarePlan]', 'yes');
        ++$this->answeredYes;

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
     * @Given I choose no and save on the plans to move client to a new residence section
     */
    public function iChooseNoOnPlansToMoveClient()
    {
        $this->selectOption('visits_care[planMoveNewResidence]', 'no');
        ++$this->answeredNo;
        $this->pressButton('Save and continue');
    }

    /**
     * @Given I choose yes and save on the plans to move client to a new residence section
     */
    public function iChooseYesOnPlansToMoveClient()
    {
        $this->selectOption('visits_care[planMoveNewResidence]', 'yes');
        ++$this->answeredYes;

        $info = 'The third set of information';
        $this->fillField('visits_care[planMoveNewResidenceDetails]', $info);
        array_push($this->additionalInfo, $info);
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
            if (1 == $this->careFundedChoice) {
                assert(
                    str_contains($entry, 'pays for all the care'),
                    sprintf('matching care funding explanation %s ', 'Client pays for all the care')
                );
            } elseif (1 == $this->careFundedChoice) {
                assert(
                    str_contains($entry, 'gets some financial help'),
                    sprintf('matching care funding explanation %s ', 'Client gets some financial help')
                );
            } elseif (1 == $this->careFundedChoice) {
                assert(
                    str_contains($entry, 'care is paid for by someone else'),
                    sprintf('matching care funding explanation %s ', 'Client\'s care is paid for by someone else')
                );
            }

            if ('no' === strtolower(trim($entry->getHtml()))) {
                ++$countNegativeResponse;
            } elseif ('yes' === strtolower(trim($entry->getHtml()))) {
                ++$countPositiveResponse;
            }
        }

        assert(
            $countNegativeResponse == $this->answeredNo,
            sprintf('Expected %d No responses, actual was %d', $this->answeredNo, $countNegativeResponse)
        );

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
        // Click on the edit button for the client receive paid care page
        $urlRegex = sprintf('/%s\/.*\/visits-care\/step\/2\?from\=summary$/', $this->reportUrlPrefix);
        $this->iClickOnNthElementBasedOnRegex($urlRegex, 1);

        $this->iAmOnVisitsCarePage2();
    }

    /**
     * @Given I view and start visits and care section
     */
    public function iViewAndStartVisitsAndCareSectionCrossBrowser()
    {
        $this->getSession()->maximizeWindow();
        $this->iViewVisitsAndCareSection();
        $this->clickLink('Start visits and care');
    }

    /**
     * @When I enter that I do not live with client
     */
    public function iDoNotLiveWithClientVisitsAndCareSectionCrossBrowser()
    {
        $this->iFillFieldForCrossBrowser('visits_care_doYouLiveWithClient_1', 'no');
    }

    /**
     * @When I can see and fill in a text box with how often I visit client
     */
    public function iCanSeeAndFillInVisitsTextBoxCrossBrowser()
    {
        $this->iFillFieldForCrossBrowser('visits_care_howOftenDoYouContactClient', 'daily');
        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCarePage2();
    }
}
