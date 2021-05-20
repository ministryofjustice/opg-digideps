<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait DeputyExpensesSectionTrait
{
    /**
     * @When I navigate to the deputy expenses report section
     */
    public function iNavigateToDeputyExpensesSection()
    {
        $this->iVisitLayStartPage();
        $this->clickLink('Start now');
        $this->clickLink('edit-deputy_expenses');

        $currentUrl = $this->getCurrentUrl();
        $onSectionPage = preg_match('/report\/.*\/deputy-expenses$/', $currentUrl);

        if (!$onSectionPage) {
            $this->throwContextualException('Not on deputy expenses section page');
        }
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
        $onSectionPage = preg_match('/report\/.*\/deputy-expenses$/', $currentUrl);

        if (!$onSectionPage) {
            $this->throwContextualException('Not on deputy expenses section page');
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
        $this->clickLink('Start deputy expenses');
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
        $this->fillInField('expenses_single[amount]', 123.12, 'expenseDetails');
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
        $this->expectedResultsDisplayedSimplified('expenseDetails');
    }
}
