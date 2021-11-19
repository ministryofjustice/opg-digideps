<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Entity\Report\Report;
use App\Tests\Behat\bootstrap\BehatException;

trait ClientBenefitsCheckSectionTrait
{
    private string $missingDateErrorText = 'Must provide a date when have checked entitlement';
    private string $missingExplanationErrorText = 'Must provide an explanation when you don\'t know if anyone else received income on clients behalf';
    private string $missingIncomeTypeErrorText = 'Please provide an income type';
    private string $atLeastOneIncomeTypeRequiredErrorText = 'Must add at least one type of income received by others if answering "yes" to "Do others receive income ion clients behalf". Use the back link if you do not have any income to declare.';

    public bool $clientBenefitsSectionAvailable = false;

    /**
     * @When I navigate to the client benefits check report section
     */
    public function iNavigateToBenefitsCheckSection()
    {
        $this->clickLink('Benefits check and income other people receive');
    }

    /**
     * @When I navigate to and start the client benefits check report section
     */
    public function iNavigateToAndStartBenefitsCheckSection()
    {
        $this->iVisitReportOverviewPage();
        $this->iNavigateToBenefitsCheckSection();
        $this->clickLink('Start');
    }

    /**
     * @When I confirm I checked the clients benefit entitlement on :dateString
     */
    public function iConfirmCheckedBenefitsOnDate(string $dateString)
    {
        $this->iAmOnClientBenefitsCheckStep1Page();

        $explodedDate = explode('/', $dateString);

        $this->chooseOption('report-client-benefits-check[whenLastCheckedEntitlement]', 'haveChecked');

        $this->fillInDateFields(
            'report-client-benefits-check[dateLastCheckedEntitlement]',
            null,
            intval($explodedDate[1]),
            intval($explodedDate[2]),
            'haveCheckedBenefits'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm I am currently checking the benefits the client is entitled to
     */
    public function iConfirmCurrentlyCheckingBenefits()
    {
        $this->iAmOnClientBenefitsCheckStep1Page();

        $this->chooseOption(
            'report-client-benefits-check[whenLastCheckedEntitlement]',
            'currentlyChecking',
            'haveCheckedBenefits',
            'I\'m currently checking this'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm I have never checked the benefits the client is entitled to and provide a reason
     */
    public function iConfirmHaveNeverCheckedBenefits()
    {
        $this->iAmOnClientBenefitsCheckStep1Page();

        $this->chooseOption(
            'report-client-benefits-check[whenLastCheckedEntitlement]',
            'neverChecked',
            'haveCheckedBenefits',
            'I\'ve never checked this'
        );

        $this->fillInField(
            'report-client-benefits-check[neverCheckedExplanation]',
            $this->faker->sentence(280),
            'haveCheckedBenefits'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm others receive income on the clients behalf
     */
    public function iConfirmOthersReceiveIncomeOnClientsBehalf()
    {
        $this->iAmOnClientBenefitsCheckStep2Page();

        $this->chooseOption(
            'report-client-benefits-check[doOthersReceiveIncomeOnClientsBehalf]',
            'yes',
            'doOthersReceiveIncome'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm others do not receive income on the clients behalf
     */
    public function iConfirmOthersDoNotReceiveIncomeOnClientsBehalf()
    {
        $this->iAmOnClientBenefitsCheckStep2Page();

        $this->chooseOption(
            'report-client-benefits-check[doOthersReceiveIncomeOnClientsBehalf]',
            'no',
            'doOthersReceiveIncome'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @Given /^I confirm I do not know if others receive income on the clients behalf and provide an explanation$/
     */
    public function iConfirmIDoNotKnowIfOthersReceiveIncomeOnTheClientsBehalfAndProvideAnExplanation()
    {
        $this->iAmOnClientBenefitsCheckStep2Page();

        $this->chooseOption(
            'report-client-benefits-check[doOthersReceiveIncomeOnClientsBehalf]',
            'dontKnow',
            'doOthersReceiveIncome',
            'I don\'t know'
        );

        $this->fillInField(
            'report-client-benefits-check[dontKnowIncomeExplanation]',
            $this->faker->sentence(20),
            'doOthersReceiveIncome',
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I add :numOfIncomeTypes type(s) of income with values
     */
    public function iAddNumberOfIncomeTypes(int $numOfIncomeTypes)
    {
        $this->iAmOnClientBenefitsCheckStep3Page();

        $numOfIncomeTypes = $numOfIncomeTypes - 1;

        foreach (range(0, $numOfIncomeTypes) as $index) {
            $this->fillInField(
                "report-client-benefits-check[typesOfIncomeReceivedOnClientsBehalf][$index][incomeType]",
                $this->faker->sentence(3),
                'incomeType'
            );

            $this->fillInField(
                "report-client-benefits-check[typesOfIncomeReceivedOnClientsBehalf][$index][amount]",
                $this->faker->numberBetween(10, 2000),
                'incomeType'
            );

            $this->pressButton('Add another');
        }
    }

    /**
     * @When I add a type of income where I don't know the value
     */
    public function iAddIncomeTypeWithNoValue()
    {
        $this->iAmOnClientBenefitsCheckStep3Page();

        $incomeTypesXpath = "//fieldset[contains(@class, 'add-another__item')]";
        $incomeTypes = $this->getSession()->getPage()->findAll('xpath', $incomeTypesXpath);

        $emptyIncomeType = null;

        foreach ($incomeTypes as $incomeType) {
            $emptyInputValueGrandparentXpath = '//input[not(@value)]/../..';
            $emptyIncomeType = $incomeType->find('xpath', $emptyInputValueGrandparentXpath) ?: null;
        }

        $incomeTypeByNameXpath = "//input[contains(@name, 'incomeType')]";
        $incomeTypeName = ($emptyIncomeType->find('xpath', $incomeTypeByNameXpath))->getAttribute('name');

        $this->fillInField($incomeTypeName, $this->faker->sentence(2), 'incomeType');

        $checkboxByNameXpath = "//input[contains(@type, 'checkbox')]";
        $checkboxName = ($emptyIncomeType->find('xpath', $checkboxByNameXpath))->getAttribute('name');

        $this->tickCheckbox(
            'incomeTypeCheckbox',
            $checkboxName,
            'incomeType',
            'I don\'t know'
        );

        $this->pressButton('Add another');
    }

    /**
     * @When I have no further types of income to add
     */
    public function iHaveNoFurtherTypesOfIncomeToAdd()
    {
        $this->iAmOnClientBenefitsCheckStep3Page();

        $this->pressButton('Save and continue');
    }

    /**
     * @When I add :numOfIncomeTypes income types from the summary page
     */
    public function iAddIncomeTypesFromSummaryPage(int $numOfIncomeTypes)
    {
        $this->iAmOnClientBenefitsCheckSummaryPage();

        $this->pressButton('Add income');

        $this->iAddNumberOfIncomeTypes($numOfIncomeTypes);
        $this->iHaveNoFurtherTypesOfIncomeToAdd();
    }

    /**
     * @When I :action the last type of income I added
     */
    public function iActionIncomeTypeIAdded(string $action)
    {
        $this->iAmOnClientBenefitsCheckSummaryPage();

        $incomeTypeAnswers = $this->getSectionAnswers('incomeType')[0];
        $incomeTypeDescription = $incomeTypeAnswers[array_key_first($incomeTypeAnswers)];

        $incomeTypeRowXpath = sprintf('//dt[contains(.,"%s")]/..', $incomeTypeDescription);
        $incomeTypeRow = $this->getSession()->getPage()->find('xpath', $incomeTypeRowXpath);

        if ('edit' === strtolower($action)) {
            $this->editFieldAnswerInSection(
                $incomeTypeRow,
                array_key_first($incomeTypeAnswers),
                $this->faker->sentence(3),
                'incomeType'
            );
        } elseif ('remove' === strtolower($action)) {
            $this->removeAnswerFromSection(
                array_key_first($incomeTypeAnswers),
                'incomeType',
                true,
                'Yes, remove income type'
            );
        } else {
            throw new BehatException('This step definition only supports "edit" and "remove"');
        }
    }

    /**
     * @Then the client benefits check summary page should contain the details I entered
     */
    public function benefitCheckSummaryPageContainsEnteredDetails()
    {
        $this->iAmOnClientBenefitsCheckSummaryPage();

        if (!is_null($this->getSectionAnswers('haveCheckedBenefits'))) {
            $this->expectedResultsDisplayedSimplified('haveCheckedBenefits');
        }

        if (!is_null($this->getSectionAnswers('doOthersReceiveIncome'))) {
            $this->expectedResultsDisplayedSimplified('doOthersReceiveIncome');
        }

        if (!is_null($this->getSectionAnswers('incomeType'))) {
            $this->expectedResultsDisplayedSimplified('incomeType');
        }
    }

    /**
     * @Given the deputies :currentOrPrevious report ends and is due :moreOrLess than 60 days after the client benefits check feature flag date
     */
    public function reportIsDueAfterClientBenefitCheckFeatureFlagDate(string $currentOrPrevious, string $moreOrLess)
    {
        $moreOrLess = strtolower($moreOrLess);

        if (!in_array($moreOrLess, ['more', 'less'])) {
            throw new BehatException(sprintf('This step only accepts "more" or "less". %s provided.', $moreOrLess));
        }

        if ('more' === $moreOrLess) {
            $this->endDateAndDueDateLoggedInUsersCurrentReportSetToDate('2040-01-01', $currentOrPrevious);
            $this->clientBenefitsSectionAvailable = true;
        } else {
            $this->endDateAndDueDateLoggedInUsersCurrentReportSetToDate('2020-01-01', $currentOrPrevious);
            $this->clientBenefitsSectionAvailable = false;
        }
    }

    /**
     * @Given they have not completed the client benefits section for their :currentOrPrevious report
     */
    public function haveNotCompletedBenefitsSection(string $currentOrPrevious)
    {
        $reportId = 'current' === $currentOrPrevious ? $this->loggedInUserDetails->getCurrentReportId() : $this->loggedInUserDetails->getPreviousReportId();

        if (empty($this->loggedInUserDetails) && empty($reportId)) {
            $message = sprintf(
                'The logged in user does not have a %s report. Ensure a user with a %s report has logged in before using this step.',
                $currentOrPrevious,
                $currentOrPrevious
            );

            throw new BehatException($message);
        }

        /** @var Report $report */
        $report = $this->em->getRepository(Report::class)->find($reportId);

        $clientBenefitsCheck = $report->getClientBenefitsCheck();
        $clientBenefitsCheck->setReport(null);

        $report->setClientBenefitsCheck(null);

        $this->em->persist($clientBenefitsCheck);
        $this->em->persist($report);
        $this->em->flush();
    }

    /**
     * @Given /^I should not see an empty section for income types$/
     */
    public function iShouldNotSeeAnEmptySectionForIncomeTypes()
    {
        $this->iAmOnClientBenefitsCheckSummaryPage();

        $incomeTypeSectionXpath = "//div[contains(@id, 'income-received')]";
        $incomeTypeDiv = $this->getSession()->getPage()->find('xpath', $incomeTypeSectionXpath);

        if (!is_null($incomeTypeDiv)) {
            throw new BehatException('The income types section appears on the page when it should not be visible');
        }
    }

    /**
     * @Given /^I confirm I checked the clients benefit entitlement but dont provide a date$/
     */
    public function iConfirmICheckedTheClientsBenefitEntitlementButDontProvideADate()
    {
        $this->iAmOnClientBenefitsCheckStep1Page();

        $this->chooseOption('report-client-benefits-check[whenLastCheckedEntitlement]', 'haveChecked');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see a :typeOfError error on client benefits check summary page
     */
    public function iShouldSeeAError(string $errorType)
    {
        switch ($errorType) {
            case 'missing date':
                $this->assertOnErrorMessage($this->missingDateErrorText);
                break;
            case 'missing explanation':
                $this->assertOnErrorMessage($this->missingExplanationErrorText);
                break;
            case 'missing income type':
                $this->assertOnErrorMessage($this->missingIncomeTypeErrorText);
                break;
            case 'at least one income type required':
                $this->assertOnErrorMessage($this->atLeastOneIncomeTypeRequiredErrorText);
                break;
            default:
                throw new BehatException('This step only supports "missing date|missing explanation|missing income type|at least one income type required". Either add a new case or update the argument.');
        }
    }

    /**
     * @Given /^I confirm I dont know if anyone else receives income on the clients behalf and dont provide an explanation$/
     */
    public function iConfirmIDontKnowIfAnyoneElseReceivesIncomeOnTheClientsBehalfButDontProvideAnExplanation()
    {
        $this->iAmOnClientBenefitsCheckStep2Page();

        $this->chooseOption(
            'report-client-benefits-check[doOthersReceiveIncomeOnClientsBehalf]',
            'dontKnow',
            'doOthersReceiveIncome'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @Given /^I confirm the amount but don't provide an income type$/
     */
    public function iConfirmTheTypeOfAnAmountButDonTProvideAnIncomeType()
    {
        $this->iAmOnClientBenefitsCheckStep3Page();

        $this->fillInField(
            'report-client-benefits-check[typesOfIncomeReceivedOnClientsBehalf][0][amount]',
            $this->faker->numberBetween(10, 2000),
            'incomeType'
        );

        $this->pressButton('Add another');
    }

    /**
     * @Given /^I change my mind and go back to the previous page$/
     */
    public function iChangeMyMindAndGoBackToThePreviousPage()
    {
        $this->iAmOnClientBenefitsCheckStep3Page();

        $this->clickLink('Back');

        $this->removeAnswerFromSection(
            'report-client-benefits-check[doOthersReceiveIncomeOnClientsBehalf]',
            'doOthersReceiveIncome'
        );
    }

    /**
     * @Given /^I attempt to submit an empty income type$/
     */
    public function iAttemptToSubmitAnEmptyIncomeType()
    {
        $this->iAmOnClientBenefitsCheckStep3Page();

        $this->pressButton('Add another');
    }
}
