<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\Reporting;

use Behat\Mink\Element\NodeElement;
use OPG\Digideps\Backend\Entity\Report\Expense;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\BaseFeatureContext;
use Behat\Step\Then;
use Behat\Step\When;

class ReportReviewFeatureContext extends BaseFeatureContext
{
    private Expense $expense;

    #[When('that report includes deputy expenses')]
    public function thatReportIncludesDeputyExpenses(): void
    {
        $this->expense = $this->fixtureHelper->createAndPersistExpense(
            $this->layDeputySubmittedPfaHighAssetsDetails->getPreviousReportId(),
            100,
            'Stationery'
        );
    }

    #[When('I view the report review page')]
    public function iViewTheReportReviewPage(): void
    {
        $reportId = $this->layDeputySubmittedPfaHighAssetsDetails->getPreviousReportId();
        $this->visitPath("/report/$reportId/review");
    }

    #[Then('I should see the submitting user\'s details in the deputy section')]
    public function iShouldSeeTheSubmittingUsersDetailsInTheDeputySection(): void
    {
        $page = $this->getSession()->getPage();

        $keys = $page->findAll('css', 'dt.govuk-summary-list__key');

        // when deputy is a lay, the deputy details shown on the report review form
        // are those of the submitter; in this case, the signed-in user
        $expectedDeputyFirstName = $this->layDeputySubmittedPfaHighAssetsDetails->getUserFirstName();
        $actualDeputyFirstName = array_values((array_filter($keys, fn (NodeElement $key) => $key->getText() === 'First names')))[0]
            ->getParent()->find('css', 'dd.govuk-summary-list__value')
            ->getText();
        $this->assertStringContainsString(
            $expectedDeputyFirstName,
            $actualDeputyFirstName,
            "Expected deputy first name '$expectedDeputyFirstName' but was '$actualDeputyFirstName'"
        );

        $expectedDeputyLastName = $this->layDeputySubmittedPfaHighAssetsDetails->getUserLastName();
        $actualDeputyLastName = array_values((array_filter($keys, fn (NodeElement $key) => $key->getText() === 'Last name')))[0]
            ->getParent()->find('css', 'dd.govuk-summary-list__value')
            ->getText();
        $this->assertStringContainsString(
            $expectedDeputyLastName,
            $actualDeputyLastName,
            "Expected deputy last name '$expectedDeputyLastName' but was '$actualDeputyLastName'"
        );
    }

    #[Then('I should see the correct details in the deputy expenses section')]
    public function iShouldSeeTheCorrectDetailsInTheDeputyExpensesSection(): void
    {
        $page = $this->getSession()->getPage();

        $titles = $page->findAll('css', 'h3.govuk-summary-card__title');
        $expenseRows = array_values((array_filter($titles, fn (NodeElement $title) => $title->getText() === 'List of expenses')))[0]
            ->getParent()->getParent()->find('css', 'tbody.govuk-table__body')?->findAll('css', 'tr.govuk-table__row') ?? [];

        $numExpensesRows = count($expenseRows) - 1;
        $this->assertIntEqualsInt(1, $numExpensesRows, "should be 1 expense shown, found $numExpensesRows");

        $expectedExplanation = $this->expense->getExplanation();
        $actualExplanation = $expenseRows[0]->findAll('css', 'td.govuk-table__cell')[0]->getText();
        $this->assertStringContainsString(
            $expectedExplanation,
            $actualExplanation,
            "Expected explanation '$expectedExplanation' but was '$actualExplanation'"
        );

        $expectedAmount = "{$this->expense->getAmount()}";
        $actualAmount = $expenseRows[0]->findAll('css', 'td.govuk-table__cell')[2]->getText();
        $this->assertStringContainsString(
            $expectedAmount,
            $actualAmount,
            "Expected amount '$expectedAmount' but was '$actualAmount'"
        );
    }
}
