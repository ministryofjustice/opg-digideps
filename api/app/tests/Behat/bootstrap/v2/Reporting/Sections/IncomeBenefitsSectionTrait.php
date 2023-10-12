<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Sections;

trait IncomeBenefitsSectionTrait
{
    /**
     * State benefits list.
     */
    private array $stateBenefits = [
        'Contributions based allowance',
        'Income Support / Pension Guarantee Credit',
        'Income-related Employment and Support Allowance',
        'Income-based Job Seeker\'s Allowance',
        'Housing benefit',
        'Universal Credit',
        'Severe Disablement Allowance',
        'Disability Living Allowance',
        'Attendance Allowance',
        'Personal Independence Payment',
        'Working and Child tax credits',
        'Other benefits',
    ];

    /**
     * @When I view and start the income benefits report section
     */
    public function iViewAndStartTheIncomeBenefitsReportSection()
    {
        $this->iVisitIncomeBenefitsSection();
        $this->pressButton('Start income and benefits');
    }

    /**
     * @When I have no other income or benefits
     */
    public function iHaveNoOtherIncomeOrBenefits()
    {
        $this->iAmOnStateBenefitsPage();
        $this->pressButton('Save and continue');

        $this->iAmOnStatePensionPage();
        $this->chooseOption('income_benefits[receiveStatePension]', 'no', 'Does John receive a state pension?');
        $this->pressButton('Save and continue');

        $this->iAmOnOtherRegularIncomePage();
        $this->chooseOption('income_benefits[receiveOtherIncome]', 'no', 'Does John receive any other regular income?');
        $this->pressButton('Save and continue');

        $this->iAmOnDamagesAndCompensationPage();
        $this->chooseOption('income_benefits[expectCompensationDamages]', 'no', 'Expecting John to get any money from compensation or damages?');
        $this->pressButton('Save and continue');

        $this->iAmOnOneOffPaymentsPage();
        $this->pressButton('Save and continue');
    }

    /**
     * @Then I should see the expected income benefits section summary
     */
    public function iShouldSeeTheExpectedIncomeBenefitsSectionSummary()
    {
        $this->iAmOnIncomeBenefitsSummaryPage();
        $this->expectedResultsDisplayedSimplified();
    }

    /**
     * @When I have 2 items from the state benefits list
     */
    public function iHave2ItemsFromTheStateBenefitsList()
    {
        $this->iAmOnStateBenefitsPage();
        $this->tickCheckbox('state-benefits', 'income_benefits[stateBenefits][1][present]', 'state-benefits', 'Income Support / Pension Guarantee Credit');
        $this->tickCheckbox('state-benefits', 'income_benefits[stateBenefits][5][present]', 'state-benefits', 'Universal Credit');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I receive a state pension
     */
    public function iReceiveAStatePension()
    {
        $this->iAmOnStatePensionPage();
        $this->chooseOption('income_benefits[receiveStatePension]', 'yes');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I don't receive any other regular income
     */
    public function iDontReceiveAnyOtherRegularIncome()
    {
        $this->iAmOnOtherRegularIncomePage();
        $this->chooseOption('income_benefits[receiveOtherIncome]', 'no');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I don't have any compensation awards or damages
     */
    public function iDontHaveAnyCompensationAwardsOrDamages()
    {
        $this->iAmOnDamagesAndCompensationPage();
        $this->chooseOption('income_benefits[expectCompensationDamages]', 'no');
        $this->pressButton('Save and continue');
    }

    /**
     * @When I have a one-off payment
     */
    public function iHaveAOneOffPayment()
    {
        $this->iAmOnOneOffPaymentsPage();
        $this->tickCheckbox(
            'one-off-payments',
            'income_benefits[oneOff][4][present]',
            'one-off-payments',
            'Sale of investment',
        );

        $this->pressButton('Save and continue');
    }

    /**
     * @When I goto the income and benefits summary page
     */
    public function iGotoTheIncomeAndBenefitsSummaryPage()
    {
        $this->iVisitIncomeBenefitsSummarySection();
        $this->iAmOnIncomeBenefitsSummaryPage();
    }

    /**
     * @When I edit state pension to say yes
     */
    public function iEditPensionsAndOtherIncomeToSayYes()
    {
        $this->getSession()->getPage()->find('css', '.behat-region-receive-state-pension')->clickLink('Edit');

        $this->iAmOnStatePensionPage();
        $this->chooseOption('income_benefits[receiveStatePension]', 'yes', 'Does John receive a state pension?');
        $this->pressButton('Save and continue');
    }
}
