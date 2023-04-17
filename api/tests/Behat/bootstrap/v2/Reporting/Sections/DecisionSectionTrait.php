<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Tests\Behat\BehatException;

// use App\Tests\Behat\MinkExtension\Context;

trait DecisionSectionTrait
{
    /**
     * @When /^I view and start the decisions report section$/
     */
    public function iViewAndStartTheDecisionsReportSection()
    {
        $this->iViewDecisionsSection();
        $this->clickLink('Start decisions');
    }

    /**
     * @Given I view the decisions report section
     */
    public function iViewDecisionsSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'decisions');

        $this->visitPath($reportSectionUrl);

        $currentUrl = $this->getCurrentUrl();
        $onSummaryPage = preg_match('/report\/.*\/decisions$/', $currentUrl);

        if (!$onSummaryPage) {
            throw new BehatException(sprintf('Not on decisions start page. Current URL is: %s', $currentUrl));
        }
    }

    /**
     * @Given /^I confirm that the clients mental capacity is the same$/
     */
    public function iConfirmThatTheClientsMentalCapacityIsTheSame()
    {
        $this->chooseOption('mental_capacity[hasCapacityChanged]', 'stayedSame', 'hasCapacityChanged');
        $this->pressButton('Save and continue');
        $this->iAmOnDecisionsPage2();
    }

    /**
     * @Given /^I confirm the clients last assessment date$/
     */
    public function iConfirmTheClientsLastAssessmentDate()
    {
        $this->fillInField('mental_assessment[mentalAssessmentDate][month]', '01', 'mentalAssessmentDate');

        $this->fillInField('mental_assessment[mentalAssessmentDate][year]', '01', 'mentalAssessmentDate');

        $this->pressButton('Save and continue');
        $this->iAmOnDecisionsPage3();
    }

    /**
     * @Given /^I confirm that no significant decisions have been made for the client$/
     */
    public function iConfirmThatNoSignificantDecisionsHaveBeenMadeForTheClient()
    {
        $this->chooseOption('decision_exist[significantDecisionsMade]', 'No', 'significantDecisionsMade');
        $this->fillInField('decision_exist[reasonForNoDecisions]', 'test', 'reasonForNoDecisions');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then /^the decisions summary page should contain the details I entered$/
     */
    public function theDecisionsSummaryPageShouldContainTheDetailsIEntered()
    {
        $this->iAmOnDecisionsSummaryPage();

        if ($this->getSectionAnswers('mental_capacity[hasCapacityChanged]')) {
            $this->expectedResultsDisplayedSimplified('hasCapacityChanged', true);
        }

        if ($this->getSectionAnswers('mental_capacity[mentalAssessmentDate]')) {
            $this->expectedResultsDisplayedSimplified('mentalAssessmentDate', true);
        }

        if ($this->getSectionAnswers('decision_exist[significantDecisionsMade]')) {
            $this->expectedResultsDisplayedSimplified('significantDecisionsMade', true);
        }

        if ($this->getSectionAnswers('decision_exist[reasonForNoDecisions]')) {
            $this->expectedResultsDisplayedSimplified('reasonForNoDecision', true);
        }
    }
}
