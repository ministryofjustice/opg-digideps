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
        $driver = $this->getSession()->getDriver();
        if ('Behat\Mink\Driver\Selenium2Driver' == get_class($driver)) {
            $this->getSession()->maximizeWindow();
        }
        $this->iViewVisitsCareSection();
        $this->clickLink('Start visits and care');
        $this->iAmOnVisitsCarePage1();
    }

    /**
     * @Given I confirm I live with the client
     */
    public function iChooseYesOnLiveWithTheClientSection()
    {
        $this->selectOption('visits_care[doYouLiveWithClient]', 'yes');
        ++$this->answeredYes;

        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCarePage2();
    }

    /**
     * @Given I confirm I do not live with the client
     */
    public function iChooseNoOnLiveWithTheClientSection()
    {
        $info = 'Information on how often there is contact with the client';

        $driver = $this->getSession()->getDriver();
        if ('Behat\Mink\Driver\Selenium2Driver' == get_class($driver)) {
            $this->iFillFieldForCrossBrowser('visits_care_doYouLiveWithClient_1', 'no');
            $this->iFillFieldForCrossBrowser('visits_care_howOftenDoYouContactClient', $info);
        } else {
            $this->selectOption('visits_care[doYouLiveWithClient]', 'no');
            $this->fillField('visits_care[howOftenDoYouContactClient]', $info);
        }

        ++$this->answeredNo;
        array_push($this->additionalInfo, $info);

        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCarePage2();
    }

    /**
     * @Given I confirm the client does not receive paid care
     */
    public function iChooseNoOnReceivePaidCareSection()
    {
        $this->selectOption('visits_care[doesClientReceivePaidCare]', 'no');
        ++$this->answeredNo;

        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCarePage3();
    }

    /**
     * @Given I confirm the client receives paid care which is funded by themselves
     */
    public function iChooseYesOnReceivePaidCareSection()
    {
        $fromSummaryPage = false;
        if ($this->iAmOnPage(sprintf('/%s\/.*\/visits-care\/step\/[0-9]\?from=summary$/', $this->reportUrlPrefix))) {
            $fromSummaryPage = true;
        }

        $this->selectOption('visits_care[doesClientReceivePaidCare]', 'yes');
        ++$this->answeredYes;

        $this->selectOption('visits_care[howIsCareFunded]', 'client_pays_for_all');
        $this->careFundedChoice = 1;

        $this->pressButton('Save and continue');

        true === $fromSummaryPage ? $this->iAmOnVisitsCareSummaryPage() : $this->iAmOnVisitsCarePage3();
    }

    /**
     * @Given I confirm the client receives paid care which is partially funded by someone else
     */
    public function iChooseYesAndOptionTwoOnReceivePaidCareSection()
    {
        $this->selectOption('visits_care[doesClientReceivePaidCare]', 'yes');
        ++$this->answeredYes;

        $this->selectOption('visits_care[howIsCareFunded]', 'client_gets_financial_help');
        $this->careFundedChoice = 2;

        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCarePage3();
    }

    /**
     * @Given I confirm the client receives paid care which is fully funded by someone else
     */
    public function iChooseYesAndOptionThreeOnReceivePaidCareSection()
    {
        $this->selectOption('visits_care[doesClientReceivePaidCare]', 'yes');
        ++$this->answeredYes;

        $this->selectOption('visits_care[howIsCareFunded]', 'all_care_is_paid_by_someone_else');
        $this->careFundedChoice = 3;

        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCarePage3();
    }

    /**
     * @Given I provide details on who is doing the caring
     */
    public function iFillOutWhoIsDoingCaringSection()
    {
        $info = 'Information on who is doing the caring';
        $this->fillField('visits_care[whoIsDoingTheCaring]', $info);
        array_push($this->additionalInfo, $info);

        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCarePage4();
    }

    /**
     * @Given I confirm the client does not have a care plan
     */
    public function iChooseNoOnHasCarePlanSection()
    {
        $this->selectOption('visits_care[doesClientHaveACarePlan]', 'no');
        ++$this->answeredNo;

        $this->pressButton('Save and continue');

        if ('ndr' == $this->reportUrlPrefix) {
            $this->iAmOnVisitsCarePage5();
        } else {
            $this->iAmOnVisitsCareSummaryPage();
        }
    }

    /**
     * @Given I confirm the client has a care plan
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

        if ('ndr' == $this->reportUrlPrefix) {
            $this->iAmOnVisitsCarePage5();
        } else {
            $this->iAmOnVisitsCareSummaryPage();
        }
    }

    /**
     * @Given I confirm there are no plans to move the client to a new residence
     */
    public function iChooseNoOnPlansToMoveClient()
    {
        $this->selectOption('visits_care[planMoveNewResidence]', 'no');
        ++$this->answeredNo;

        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCareSummaryPage();
    }

    /**
     * @Given I confirm there are plans to to move the client to a new residence
     */
    public function iChooseYesOnPlansToMoveClient()
    {
        $this->selectOption('visits_care[planMoveNewResidence]', 'yes');
        ++$this->answeredYes;

        $info = 'Information on plans to move the client';
        $this->fillField('visits_care[planMoveNewResidenceDetails]', $info);
        array_push($this->additionalInfo, $info);

        $this->pressButton('Save and continue');
        $this->iAmOnVisitsCareSummaryPage();
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
        $comparisonSubject = 'Care funding explanation';
        foreach ($tableEntry as $entry) {
            if (1 == $this->careFundedChoice) {
                $this->assertStringContainsString('pays for all the care', $entry, $comparisonSubject);
            } elseif (1 == $this->careFundedChoice) {
                $this->assertStringContainsString('gets some financial help', $entry, $comparisonSubject);
            } elseif (1 == $this->careFundedChoice) {
                $this->assertStringContainsString('care is paid for by someone else', $entry, $comparisonSubject);
            }

            if ('no' === strtolower(trim($entry->getHtml()))) {
                ++$countNegativeResponse;
            } elseif ('yes' === strtolower(trim($entry->getHtml()))) {
                ++$countPositiveResponse;
            }
        }

        $this->assertIntEqualsInt($this->answeredNo, $countNegativeResponse, 'Number of Yes Responses');
        $this->assertIntEqualsInt($this->answeredYes, $countPositiveResponse, 'Number of No Responses');

        $this->iShouldSeeTheExpectedVisitCareAdditionalInfo();
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
            $this->assertStringContainsString($info, $table->getHtml(), 'Written responses');
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
}
