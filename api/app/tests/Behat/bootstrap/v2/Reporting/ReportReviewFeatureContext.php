<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting;

use App\Entity\Report\Expense;
use App\Tests\Behat\v2\Common\BaseFeatureContext;
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

        // when deputy is a lay, the deputy details shown on the report review form
        // are those of the submitter; in this case, the signed-in user
        $expectedDeputyFirstName = $this->layDeputySubmittedPfaHighAssetsDetails->getUserFirstName();
        $actualDeputyFirstName = $page->find('css', 'dd.behat-region-deputy-firstname')->getText();
        $this->assertStringContainsString(
            $expectedDeputyFirstName,
            $actualDeputyFirstName,
            "Expected deputy first name '$expectedDeputyFirstName' but was '$actualDeputyFirstName'"
        );

        $expectedDeputyLastName = $this->layDeputySubmittedPfaHighAssetsDetails->getUserLastName();
        $actualDeputyLastName = $page->find('css', 'dd.behat-region-deputy-lastname')->getText();
        $this->assertStringContainsString(
            $expectedDeputyLastName,
            $actualDeputyLastName,
            "Expected deputy first name '$expectedDeputyLastName' but was '$actualDeputyLastName'"
        );
    }

    #[Then('I should see the correct details in the deputy expenses section')]
    public function iShouldSeeTheCorrectDetailsInTheDeputyExpensesSection(): void
    {
        $expenseRows = $this->getSession()->getPage()->findAll('css', 'tr[data-role="deputy-expense"]');
        $numExpensesRows = count($expenseRows);
        $this->assertIntEqualsInt(1, $numExpensesRows, "should be 1 expense shown, found $numExpensesRows");

        $expectedExplanation = $this->expense->getExplanation();
        $actualExplanation = $expenseRows[0]->find('css', 'td.label')->getText();
        $this->assertStringContainsString(
            $expectedExplanation,
            $actualExplanation,
            "Expected explanation '$expectedExplanation' but was '$actualExplanation'"
        );

        $expectedAmount = "{$this->expense->getAmount()}";
        $actualAmount = $expenseRows[0]->find('css', 'td.value')->getText();
        $this->assertStringContainsString(
            $expectedAmount,
            $actualAmount,
            "Expected amount '$expectedAmount' but was '$actualAmount'"
        );
    }
}
