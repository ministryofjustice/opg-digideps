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

        $this->fillInField('mental_assessment[mentalAssessmentDate][year]', '2010', 'mentalAssessmentDate');

        $this->pressButton('Save and continue');
        $this->iAmOnDecisionsPage3();
    }

    /**
     * @Given I confirm that :response significant decisions have been made for the client
     */
    public function iConfirmThatNoSignificantDecisionsHaveBeenMadeForTheClient(string $response)
    {
        $this->chooseOption('decision_exist[significantDecisionsMade]', $response, 'significantDecisionsMade');
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

    /**
     * @Given /^I edit my response to the significant decisions question to \'([^\']*)\'$/
     */
    public function iEditMyResponseToTheSignificantDecisionsQuestionTo(string $response)
    {
        $this->clickLink('significantDecisionsEdit');
        $this->chooseOption('decision_exist[significantDecisionsMade]', $response, 'significantDecisionsMade');

        $this->pressButton('Save and continue');
        $this->iAmOnDecisionsPage4();
    }

    /**
     * @Given /^I add the details of the decision as requested$/
     */
    public function iAddTheDetailsOfTheDecisionAsRequested()
    {
        $this->fillInField('decision[description]', 'Decision entered', 'description');
        $this->chooseOption('decision[clientInvolvedBoolean]', '0', 'clientInvolvedDetails');
        $this->fillInField('decision[clientInvolvedDetails]', 'Decision entered', 'description');

        $this->pressButton('Save and continue');
        $this->iAmOnDecisionsPage5();

        $this->chooseOption('add_another[addAnother]', 'no', 'addAnother');
        $this->pressButton('Continue');
    }

    /**
     * @Then /^the decisions summary page should reflect the updated details I entered$/
     */
    public function theDecisionsSummaryPageShouldReflectTheUpdatedDetailsIEntered()
    {
        $this->iAmOnDecisionsSummaryPage();

        if ($this->getSectionAnswers('decision_exist[significantDecisionsMade]')) {
            $this->expectedResultsDisplayedSimplified('significantDecisionsMade', true);
        }

        if ($this->getSectionAnswers('decision[description]')) {
            $this->expectedResultsDisplayedSimplified('description', true);
        }

        if ($this->getSectionAnswers('decision[clientInvolvedBoolean]')) {
            $this->expectedResultsDisplayedSimplified('clientInvolvedDetails', true);
        }

        $this->assertReasonForNoDecisionsIsNotVisible(true);
    }

    private function assertReasonForNoDecisionsIsNotVisible(bool $shouldNotBeVisible)
    {
        $reasonForNoDecisionPath = './/label[text()[contains(.,"Reason for no decisions")]]/..';

        $reasonForNoDecisionDiv = $this->getSession()->getPage()->find('xpath', $reasonForNoDecisionPath);

        $reasonForNoDecisionIsNotVisible = is_null($reasonForNoDecisionDiv);

        if ($shouldNotBeVisible) {
            if (!$reasonForNoDecisionIsNotVisible) {
                $message = sprintf('The reason for no decision box is visible on the summary page when it shouldn\'t be: %s', $reasonForNoDecisionDiv->getHtml()
                );

                throw new BehatException($message);
            }
        }
    }
}
