<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Entity\Report\Report;
use App\Tests\Behat\BehatException;

trait ClientBenefitsCheckSectionTrait
{
    public bool $clientBenefitsSectionAvailable = true;
    private string $missingDateErrorText = 'Enter the date you last checked %s\'s benefits';
    private string $missingExplanationErrorText = 'Tell us why you don\'t know if anyone other than you received money on %s\'s behalf';
    private string $missingMoneyTypeErrorText = 'Enter the type of payment';
    private string $missingWhoReceivedMoneyErrorText = 'Enter the name of the person or organisation who received the money';
    private string $atLeastOneMoneyTypeRequiredErrorText = 'Enter at least one payment';

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
     * @When I navigate to the client benefits check report section
     */
    public function iNavigateToBenefitsCheckSection()
    {
        $this->clickLink('Benefits check and money others received');
    }

    /**
     * @When I confirm I checked the clients benefit entitlement on :dateString
     */
    public function iConfirmCheckedBenefitsOnDate(string $dateString)
    {
        $this->iAmOnClientBenefitsCheckStep1Page();

        $explodedDate = explode('/', $dateString);

        // When choosing the date only a date is show, not a translated field option response
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

        $clientFirstName = $this->loggedInUserDetails->getClientFirstName();

        $this->chooseOption(
            'report-client-benefits-check[whenLastCheckedEntitlement]',
            'currentlyChecking',
            'haveCheckedBenefits',
            sprintf('I have begun to check and am waiting to find out if %s', $clientFirstName),
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
            'No, I did not check this'
        );

        $this->fillInField(
            'report-client-benefits-check[neverCheckedExplanation]',
            $this->faker->sentence(280),
            'haveCheckedBenefits'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm others receive money on the clients behalf
     */
    public function iConfirmOthersReceiveMoneyOnClientsBehalf()
    {
        $this->iAmOnClientBenefitsCheckStep2Page();

        $this->chooseOption(
            'report-client-benefits-check[doOthersReceiveMoneyOnClientsBehalf]',
            'yes',
            'doOthersReceiveMoney'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I confirm others do not receive money on the clients behalf
     */
    public function iConfirmOthersDoNotReceiveMoneyOnClientsBehalf()
    {
        $this->iAmOnClientBenefitsCheckStep2Page();

        $this->chooseOption(
            'report-client-benefits-check[doOthersReceiveMoneyOnClientsBehalf]',
            'no',
            'doOthersReceiveMoney'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @Given /^I confirm I do not know if others receive money on the clients behalf and provide an explanation$/
     */
    public function iConfirmIDoNotKnowIfOthersReceiveMoneyOnTheClientsBehalfAndProvideAnExplanation()
    {
        $this->iAmOnClientBenefitsCheckStep2Page();

        $this->chooseOption(
            'report-client-benefits-check[doOthersReceiveMoneyOnClientsBehalf]',
            'dontKnow',
            'doOthersReceiveMoney',
            'I don\'t know'
        );

        $this->fillInField(
            'report-client-benefits-check[dontKnowMoneyExplanation]',
            $this->faker->sentence(20),
            'doOthersReceiveMoney',
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I add a type of money where I don't know the value
     */
    public function iAddMoneyTypeWithNoValue()
    {
        $this->iAmOnClientBenefitsCheckStep3Page();

        $moneyTypesXpath = "//fieldset[contains(@class, 'add-another__item')]";
        $moneyTypes = $this->getSession()->getPage()->findAll('xpath', $moneyTypesXpath);

        $emptyMoneyType = null;

        foreach ($moneyTypes as $moneyType) {
            $emptyInputValueGrandparentXpath = '//input[not(@value)]/../..';
            $emptyMoneyType = $moneyType->find('xpath', $emptyInputValueGrandparentXpath) ?: null;
        }

        $moneyTypeByNameXpath = "//input[contains(@name, 'moneyType')]";
        $moneyTypeName = $emptyMoneyType->find('xpath', $moneyTypeByNameXpath)->getAttribute('name');

        $this->fillInField($moneyTypeName, $this->faker->sentence(2), 'moneyType');

        $whoReceivedMoneyByNameXpath = "//input[contains(@name, 'whoReceivedMoney')]";
        $whoReceivedMoneyInput = $emptyMoneyType->find('xpath', $whoReceivedMoneyByNameXpath)->getAttribute('name');

        $this->fillInField($whoReceivedMoneyInput, $this->faker->sentence(2), 'moneyType');

        $checkboxByTypeXpath = "//input[contains(@type, 'checkbox')]";
        $checkboxName = $emptyMoneyType->find('xpath', $checkboxByTypeXpath)->getAttribute('name');

        $this->tickCheckbox(
            'moneyTypeCheckbox',
            $checkboxName,
            'moneyType',
            'I don\'t know'
        );

        $this->pressButton('Add another');
    }

    /**
     * @When I add :numOfMoneyTypes money types from the summary page
     */
    public function iAddMoneyTypesFromSummaryPage(int $numOfMoneyTypes)
    {
        $this->iAmOnClientBenefitsCheckSummaryPage();

        $this->pressButton('Add money');

        $this->iAddNumberOfMoneyTypes($numOfMoneyTypes);
        $this->iHaveNoFurtherTypesOfMoneyToAdd();
    }

    /**
     * @When I add :numOfMoneyTypes type(s) of money with values
     */
    public function iAddNumberOfMoneyTypes(int $numOfMoneyTypes)
    {
        $this->iAmOnClientBenefitsCheckStep3Page();

        $numOfMoneyTypes = $numOfMoneyTypes - 1;

        foreach (range(0, $numOfMoneyTypes) as $index) {
            $this->fillInField(
                "report-client-benefits-check[typesOfMoneyReceivedOnClientsBehalf][$index][moneyType]",
                $this->faker->sentence(3),
                'moneyType'
            );

            $this->fillInField(
                "report-client-benefits-check[typesOfMoneyReceivedOnClientsBehalf][$index][whoReceivedMoney]",
                $this->faker->sentence(2),
                'moneyType'
            );

            $this->fillInField(
                "report-client-benefits-check[typesOfMoneyReceivedOnClientsBehalf][$index][amount]",
                $this->faker->numberBetween(10, 2000),
                'moneyType'
            );

            $this->pressButton('Add another');
        }
    }

    /**
     * @When I have no further types of money to add
     */
    public function iHaveNoFurtherTypesOfMoneyToAdd()
    {
        $this->iAmOnClientBenefitsCheckStep3Page();

        $this->pressButton('Save and continue');
    }

    /**
     * @When I :action the last type of money I added
     */
    public function iActionMoneyTypeIAdded(string $action)
    {
        $this->iAmOnClientBenefitsCheckSummaryPage();

        $moneyTypeAnswers = $this->getSectionAnswers('moneyType')[0];
        $moneyTypeDescription = $moneyTypeAnswers[array_key_first($moneyTypeAnswers)];

        $moneyTypeRowXpath = sprintf('//dd[contains(.,"%s")]/..', $moneyTypeDescription);
        $moneyTypeRow = $this->getSession()->getPage()->find('xpath', $moneyTypeRowXpath);

        if ('edit' === strtolower($action)) {
            $this->editFieldAnswerInSection(
                $moneyTypeRow,
                array_key_first($moneyTypeAnswers),
                $this->faker->sentence(3),
                'moneyType'
            );
        } elseif ('remove' === strtolower($action)) {
            $this->removeAnswerFromSection(
                array_key_first($moneyTypeAnswers),
                'moneyType',
                true,
                'Yes, remove money type'
            );
        } else {
            throw new BehatException('This step definition only supports "edit" and "remove"');
        }
    }

    /**
     * @Then the client benefits check summary page should contain the details I entered
     * @Then the client benefits check summary page should contain my updated response and no money types
     */
    public function benefitCheckSummaryPageContainsEnteredDetails()
    {
        $this->iAmOnClientBenefitsCheckSummaryPage();

        if (!is_null($this->getSectionAnswers('haveCheckedBenefits'))) {
            $this->expectedResultsDisplayedSimplified('haveCheckedBenefits', true);
        }

        if (!is_null($this->getSectionAnswers('doOthersReceiveMoney'))) {
            $this->expectedResultsDisplayedSimplified('doOthersReceiveMoney', true);
        }

        if (!is_null($this->getSectionAnswers('moneyType'))) {
            $this->expectedResultsDisplayedSimplified('moneyType', true);
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
     * @Given /^I should not see an empty section for money types$/
     */
    public function iShouldNotSeeAnEmptySectionForMoneyTypes()
    {
        $this->iAmOnClientBenefitsCheckSummaryPage();

        $moneyTypeSectionXpath = "//div[contains(@id, 'money-received')]";
        $moneyTypeDiv = $this->getSession()->getPage()->find('xpath', $moneyTypeSectionXpath);

        if (!is_null($moneyTypeDiv)) {
            throw new BehatException('The money types section appears on the page when it should not be visible');
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
                $this->assertOnErrorMessage(sprintf($this->missingDateErrorText, $this->loggedInUserDetails->getClientFirstName()));
                break;
            case 'missing explanation':
                $this->assertOnErrorMessage(sprintf($this->missingExplanationErrorText, $this->loggedInUserDetails->getClientFirstName()));
                break;
            case 'missing money type':
                $this->assertOnErrorMessage($this->missingMoneyTypeErrorText);
                break;
            case 'missing who received money':
                $this->assertOnErrorMessage($this->missingWhoReceivedMoneyErrorText);
                break;
            case 'at least one money type required':
                $this->assertOnErrorMessage($this->atLeastOneMoneyTypeRequiredErrorText);
                break;
            default:
                throw new BehatException('This step only supports "missing date|missing explanation|missing money type|at least one money type required". Either add a new case or update the argument.');
        }
    }

    /**
     * @Given /^I confirm I dont know if anyone else receives money on the clients behalf and dont provide an explanation$/
     */
    public function iConfirmIDontKnowIfAnyoneElseReceivesMoneyOnTheClientsBehalfButDontProvideAnExplanation()
    {
        $this->iAmOnClientBenefitsCheckStep2Page();

        $this->chooseOption(
            'report-client-benefits-check[doOthersReceiveMoneyOnClientsBehalf]',
            'dontKnow',
            'doOthersReceiveMoney'
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @Given /^I confirm the amount but don't provide a money type$/
     */
    public function iConfirmTheTypeOfAnAmountButDonTProvideAMoneyType()
    {
        $this->iAmOnClientBenefitsCheckStep3Page();

        $this->fillInField(
            'report-client-benefits-check[typesOfMoneyReceivedOnClientsBehalf][0][amount]',
            $this->faker->numberBetween(10, 2000),
            'moneyType'
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
            'report-client-benefits-check[doOthersReceiveMoneyOnClientsBehalf]',
            'doOthersReceiveMoney'
        );
    }

    /**
     * @Given /^I attempt to submit an empty money type$/
     */
    public function iAttemptToSubmitAnEmptyMoneyType()
    {
        $this->iAmOnClientBenefitsCheckStep3Page();

        $this->pressButton('Add another');
    }

    /**
     * @Given I edit my response to do others receive money on a clients behalf to :response
     */
    public function iEditMyResponseToDoOthersReceiveMoneyOnAClientsBehalf(string $response)
    {
        $this->iAmOnClientBenefitsCheckSummaryPage();

        $clientFirstName = $this->loggedInUserDetails->getClientFirstName();
        $questionText = sprintf('Did anyone receive any money from people or organisations on %s', $clientFirstName);
        $questionRowXpath = sprintf("//dt[contains(., '%s')]/..", $questionText);
        $questionRow = $this->getSession()->getPage()->find('xpath', $questionRowXpath);

        if (is_null($questionRow)) {
            $message = sprintf('A row on the page with the question "%s" could not be found', $questionText);
            throw new BehatException($message);
        }

        $this->editFieldAnswerInSection(
            $questionRow,
            'report-client-benefits-check[doOthersReceiveMoneyOnClientsBehalf]',
            $response,
            'doOthersReceiveMoney'
        );

        $this->removeAnswerFromSection(
            'report-client-benefits-check[typesOfMoneyReceivedOnClientsBehalf][0][moneyType]',
            'moneyType'
        );
    }

    /**
     * @Given I edit my response to when I last checked the clients benefit entitlement to currently checking
     */
    public function iEditMyResponseToWhenILastCheckedTheClientsBenefitEntitlement()
    {
        $this->iAmOnClientBenefitsCheckSummaryPage();

        $clientFirstName = $this->loggedInUserDetails->getClientFirstName();
        $questionText = sprintf('Did you check that %s gets all the benefits they should have in the last reporting period', $clientFirstName);
        $questionRowXpath = sprintf("//dt[contains(., '%s')]/..", $questionText);
        $questionRow = $this->getSession()->getPage()->find('xpath', $questionRowXpath);

        if (is_null($questionRow)) {
            $message = sprintf('A row on the page with the question "%s" could not be found', $questionText);
            throw new BehatException($message);
        }

        $this->editSelectAnswerInSection(
            $questionRow,
            'report-client-benefits-check[whenLastCheckedEntitlement]',
            'currentlyChecking',
            'haveCheckedBenefits',
            sprintf('I have begun to check and am waiting to find out if %s', $clientFirstName)
        );
    }

    /**
     * @Given /^I fill in amount and description but dont provide details on who received the money$/
     */
    public function iDontProvideDetailsOnWhoReceivedTheMoney()
    {
        $this->fillInField(
            'report-client-benefits-check[typesOfMoneyReceivedOnClientsBehalf][0][moneyType]',
            $this->faker->sentence(3),
            'moneyType'
        );

        $this->fillInField(
            'report-client-benefits-check[typesOfMoneyReceivedOnClientsBehalf][0][amount]',
            $this->faker->numberBetween(10, 2000),
            'moneyType'
        );

        $this->pressButton('Add another');
    }
}
