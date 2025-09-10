<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

use App\Entity\User;
use App\Tests\Behat\BehatException;

trait DeputyExpensesSectionTrait
{
    private string $sectionStartText = 'Have you claimed any deputy expenses during this reporting period?';

    private string $notANumberError = 'Enter the amount of the expense in numbers';
    private string $outOfRangeError = 'The amount must be between £0.01 and £100,000,000,000';
    private string $missingDescriptionError = 'Please enter a description';
    private string $missingAmountError = 'Please enter an amount';

    /**
     * @When I navigate to and start the deputy expenses report section
     */
    public function iNavigateToAndStartDeputyExpensesSection()
    {
        $this->iVisitReportOverviewPage();
        $this->clickLink('edit-deputy_expenses');

        $currentUrl = $this->getCurrentUrl();
        $reportPrefix = $this->loggedInUserDetails->getCurrentReportNdrOrReport();
        $onSectionPage = preg_match("/{$reportPrefix}\/.*\/deputy-expenses$/", $currentUrl);

        if (!$onSectionPage) {
            throw new BehatException('Not on deputy expenses section page');
        }

        $this->clickLink('Start deputy expenses');
    }

    /**
     * @When I view the deputy expenses report section
     */
    public function iViewDeputyExpensesSection()
    {
        $activeReportId = $this->loggedInUserDetails->getCurrentReportId();
        $reportSectionUrl = sprintf(self::REPORT_SECTION_ENDPOINT, $this->reportUrlPrefix, $activeReportId, 'deputy-expenses');

        $this->visitPath($reportSectionUrl);

        $currentUrl = $this->getCurrentUrl();
        $reportPrefix = $this->loggedInUserDetails->getCurrentReportNdrOrReport();
        $onSectionPage = preg_match("/{$reportPrefix}\/.*\/deputy-expenses$/", $currentUrl);

        if (!$onSectionPage) {
            throw new BehatException('Not on deputy expenses section page');
        }
    }

    /**
     * @When I view and start the deputy expenses report section
     */
    public function iViewAndStartDeputyExpensesSection()
    {
        $this->iViewDeputyExpensesSection();
        $this->clickLink('Start deputy expenses');
    }

    /**
     * @When I have no expenses to declare
     */
    public function iHaveNoExpenses()
    {
        $this->chooseOption('yes_no[paidForAnything]', 'no', 'anyExpensesClaimed');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I have expenses to declare
     */
    public function iHaveExpenses()
    {
        $this->chooseOption('yes_no[paidForAnything]', 'yes', 'anyExpensesClaimed');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I enter valid expense details
     */
    public function iEnterValidExpenses()
    {
        $this->fillInField('expenses_single[explanation]', $this->faker->sentence(12), 'expenseDetails');
        $this->fillInFieldTrackTotal('expenses_single[amount]', 981, 'expenseDetails');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I declare another expense
     */
    public function iDeclareAnotherExpenses()
    {
        $this->chooseOption('add_another[addAnother]', 'yes');
        $this->pressButton('Continue');

        $this->fillInField('expenses_single[explanation]', $this->faker->sentence(12), 'expenseDetails');
        $this->fillInFieldTrackTotal('expenses_single[amount]', 22, 'expenseDetails');
        $this->pressButton('Save and continue');
    }

    /**
     * @When there are no further expenses to add
     */
    public function noFurtherExpensesToAdd()
    {
        $this->fillInField('add_another[addAnother]', 'no');
        $this->pressButton('Continue');
    }

    /**
     * @Then the expenses summary page should contain the details I entered
     */
    public function expensesSummaryPageContainsEnteredDetails()
    {
        $this->expectedResultsDisplayedSimplified('anyExpensesClaimed');

        if (!is_null($this->getSectionAnswers('expenseDetails'))) {
            $this->expectedResultsDisplayedSimplified('expenseDetails', false, false);
        }
    }

    /**
     * @When I enter the wrong type of values
     */
    public function iEnterWrongValueTypes()
    {
        $this->fillInField('expenses_single[explanation]', 764.98, 'expenseDetails');
        $this->fillInField('expenses_single[amount]', $this->faker->sentence(12), 'expenseDetails');
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see 'type validation' errors
     */
    public function iShouldSeeTypeValidationError()
    {
        $this->assertOnErrorMessage($this->notANumberError);
    }

    /**
     * @When I don't enter any values
     */
    public function iDoNotEnterValues()
    {
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see 'missing values' errors
     */
    public function iShouldSeeMissingValuesErrors()
    {
        $this->assertOnErrorMessage($this->missingAmountError);
        $this->assertOnErrorMessage($this->missingDescriptionError);
    }

    /**
     * @When I enter an expense amount that is too high
     */
    public function iEnterValueToHigh()
    {
        $this->fillInField('expenses_single[explanation]', $this->faker->sentence(12), 'expenseDetails');
        $this->fillInField('expenses_single[amount]', 100000000000.01, 'expenseDetails');

        $this->pressButton('Save and continue');
    }

    /**
     * @When I enter an expense amount that is too low
     */
    public function iEnterValueToLow()
    {
        $this->fillInField('expenses_single[explanation]', $this->faker->sentence(12), 'expenseDetails');
        $this->fillInField('expenses_single[amount]', 0, 'expenseDetails');

        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see an 'amount out of range' error
     */
    public function iShouldSeeAmountOutOfRangeError()
    {
        $this->assertOnErrorMessage($this->outOfRangeError);
    }

    /**
     * @When I edit the expense details
     */
    public function iEditTheExpense()
    {
        $answers = $this->getSectionAnswers('expenseDetails')[0];

        if ('ndr' == $this->reportUrlPrefix) {
            $rowSelector = sprintf('//div[dt[normalize-space() ="%s"]]', $answers['expenses_single[explanation]']);
        } else {
            $rowSelector = sprintf('//tr[td[normalize-space() ="%s"]]', $answers['expenses_single[explanation]']);
        }
        $descriptionTableRow = $this->getSession()->getPage()->find('xpath', $rowSelector);

        $this->editFieldAnswerInSectionTrackTotal($descriptionTableRow, 'expenses_single[amount]', 'expenseDetails');
    }

    /**
     * @When I remove an expense I declared
     */
    public function iRemoveAnExpense()
    {
        $this->removeAnswerFromSection(
            'expenses_single[amount]',
            'expenseDetails',
            true,
            'Yes, remove expense'
        );
    }

    /**
     * @When I add an expense from the expense summary page
     */
    public function iAddExpenseFromSummaryPage()
    {
        $this->clickLink('Add a deputy expense');

        $this->fillInField('expenses_single[explanation]', $this->faker->sentence(12), 'expenseDetails');
        $this->fillInFieldTrackTotal('expenses_single[amount]', 578, 'expenseDetails');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I change my mind and answer no to expenses to declare
     */
    public function iChangeMindAnswerNoToExpensesToDeclare()
    {
        if ('ndr' == $this->reportUrlPrefix) {
            $id = $this->interactingWithUserDetails->getUserId();
            $user = $this->em->getRepository(User::class)->find($id);

            $this->sectionStartText = sprintf('Did you pay for anything for %s before you got your court order?', $user->getFirstname());
        }

        $rowSelector = sprintf(
            '//div[dt[normalize-space() ="%s"]]',
            $this->sectionStartText
        );

        $descriptionTableRow = $this->getSession()->getPage()->find('css', '.behat-region-paid-for-anything .behat-link-edit');
        $descriptionTableRow->click();

        $this->removeAllAnswers();
        $this->iHaveNoExpenses();
    }
}
