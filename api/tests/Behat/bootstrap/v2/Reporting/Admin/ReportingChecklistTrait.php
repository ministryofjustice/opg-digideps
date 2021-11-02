<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Admin;

use App\Entity\Report\Report;

trait ReportingChecklistTrait
{
    // Amount of required checklist fields
    private int $requiredChecklistFields = 12;

    // Errors
    private array $formErrors = [
        "Please select 'Yes' or 'No'",
        "Confirm that you've checked the contact details are correct",
        "Confirm that you've checked the deputy's full name on CASREC is correct",
        "Please select 'Yes' or 'No'",
        "Please select 'Yes' or 'No'",
        "Please select 'Yes' or 'No'",
        "Please select 'Yes' or 'No'",
        'Please choose an option',
        'Please enter all your concerns and decisions',
        'Please choose an option',
    ];

    // Sidebar items
    private array $xPathItems = [
        'deputyClient' => "//a[@href='#profDeputyCostsEstimate']", // lay, pa, prof
        'decisionsMade' => "//a[@href='#decisionsMade']", // lay, pa, prof
        'peopleConsulted' => "//a[@href='#peopleConsulted']", // lay, pa, prof
        'visitsAndCare' => "//a[@href='#visitsAndCare']", // lay, pa, prof
        'lifestyle' => "//a[@href='#lifestyle']", // lay-hw
        'assetsAndDebts' => "//a[@href='#assetsAndDebts']", // lay-assets, pa-assets, prof-assets
        'clientBenefitsCheck' => "//a[@href='#clientBenefitsCheck']",
        'moneyInOut' => "//a[@href='#moneyInOut']", // lay-assets, pa-assets, prof-assets
        'bonds' => "//a[@href='#bonds']", // lay-assets, pa-assets, prof-assets
        'profDeputyCosts' => "//a[@href='#profDeputyCosts']", // prof
        'profDeputyCostsEstimate' => "//a[@href='#profDeputyCostsEstimate']", // prof
        'paFeesExpenses' => "//a[@href='#paFeesExpenses']", // pa
        'nextReportingPeriod' => "//a[@href='#nextReportingPeriod']", // lay, pa, prof
        'declaration' => "//a[@href='#declaration']", // lay, pa, prof
        'anchorLodgingSummary' => "//a[@href='#anchor-lodging-summary']", // lay, pa, prof
        'anchorFurtherInformation' => "//a[@href='#anchor-further-information']", // lay, pa, prof
    ];

    /**
     * @When I navigate to the clients search page
     */
    public function iNavigateToTheClientsSearchPage()
    {
        $this->iVisitAdminClientSearchPage();
        $this->iAmOnAdminClientsSearchPage();
    }

    /**
     * @When I search for the client I'm interacting with
     */
    public function iSearchForTheClient()
    {
        $this->assertInteractingWithUserIsSet();
        $this->searchAdminForClientWithTerm($this->interactingWithUserDetails->getClientCaseNumber());
    }

    /**
     * @When I click the clients details page link
     */
    public function iClickTheClientsDetailsPageLink()
    {
        $this->iClickOnNthElementBasedOnRegex('/admin\/client\/.*\/details$/', 0);

        $this->iAmOnAdminClientDetailsPage();
    }

    /**
     * @When I navigate to the clients report checklist page
     */
    public function iNavigateToTheClientsReportChecklistPage()
    {
        $this->iNavigateToTheReportChecklistPage();
    }

    /**
     * @When I submit the checklist without filling it in
     */
    public function iSubmitTheChecklistWithoutFillingItIn()
    {
        $this->pressButton('report_checklist_submitAndContinue');
    }

    /**
     * @When I submit the checklist with the form filled in
     */
    public function iSubmitTheChecklistWithTheFormFilledIn()
    {
        $reportType = $this->interactingWithUserDetails->getCurrentReportType();

        $this->selectOption('report_checklist[reportingPeriodAccurate]', 'yes');
        $this->checkOption('report_checklist[contactDetailsUptoDate]');
        $this->checkOption('report_checklist[deputyFullNameAccurateInCasrec]');

        if (in_array($reportType, Report::allRolesHwAndCombinedReportTypes())) {
            $this->selectOption('report_checklist[decisionsSatisfactory]', 'yes');
            $this->selectOption('report_checklist[consultationsSatisfactory]', 'yes');
            $this->selectOption('report_checklist[careArrangements]', 'yes');
            $this->selectOption('report_checklist[satisfiedWithHealthAndLifestyle]', 'yes');
            $this->selectOption('report_checklist[futureSignificantDecisions]', 'yes');
            $this->selectOption('report_checklist[hasDeputyRaisedConcerns]', 'yes');
        }

        if (in_array($reportType, Report::allRolesPfaAndCombinedReportTypes())) {
            $this->selectOption('report_checklist[clientBenefitsChecked]', 'yes');
            $this->selectOption('report_checklist[assetsDeclaredAndManaged]', 'yes');
            $this->selectOption('report_checklist[debtsManaged]', 'yes');
            $this->selectOption('report_checklist[openClosingBalancesMatch]', 'yes');
            $this->selectOption('report_checklist[accountsBalance]', 'yes');
            $this->selectOption('report_checklist[moneyMovementsAcceptable]', 'yes');
            $this->selectOption('report_checklist[bondAdequate]', 'yes');
            $this->selectOption('report_checklist[bondOrderMatchCasrec]', 'yes');
        }

        $this->selectOption('report_checklist[caseWorkerSatisified]', 'yes');
        $this->fillField('report_checklist[lodgingSummary]', 'Lorem ipsum');
        $this->selectOption('report_checklist[finalDecision]', 'satisfied');

        $this->pressButton('report_checklist[submitAndContinue]');
    }

    /**
     * @Then I should see all the validation errors
     */
    public function iShouldSeeAllTheValidationErrors()
    {
        foreach ($this->formErrors as $error) {
            $this->assertOnErrorMessage($error);
        }
    }

    /**
     * @Then I should be redirected to the checklist submitted page
     */
    public function iShouldBeRedirectedToTheChecklistSubmittedPage()
    {
        $this->iAmOnAdminReportChecklistSubmittedPage();
        $savedText = $this->getSession()->getPage()->find('css', '.opg-alert__message > p')->getText();

        assert('Lodging checklist saved' == $savedText);
    }

    /**
     * @Then I can only see the :deputyType specific section
     */
    public function ICannotSeeTheSpecificCostsSection(string $deputyType)
    {
        if ('public authority pfa high' === $deputyType) {
            $hiddenItems = [
                'lifestyle',
                'profDeputyCosts',
                'profDeputyCostsEstimate',
            ];

            foreach ($this->xPathItems as $xPathitem) {
                foreach ($hiddenItems as $hiddenItem) {
                    if ($hiddenItem === $xPathitem) {
                        $link = $this->getSession()->getPage()->find('xpath', $xPathitem);
                        assert(null === $link);
                    }
                }
            }
        } elseif ('lay hw' === $deputyType) {
            $hiddenItems = [
                'assetsAndDebts',
                'moneyInOut',
                'bonds',
                'profDeputyCosts',
                'profDeputyCostsEstimate',
                'paFeesExpenses',
                'clientBenefitsCheck',
            ];

            foreach ($this->xPathItems as $xPathitem) {
                foreach ($hiddenItems as $hiddenItem) {
                    if ($hiddenItem === $xPathitem) {
                        $link = $this->getSession()->getPage()->find('xpath', $xPathitem);
                        assert(null === $link);
                    }
                }
            }
        } elseif ('prof pfa high' === $deputyType) {
            $hiddenItems = [
                'lifestyle',
                'paFeesExpenses',
            ];

            foreach ($this->xPathItems as $xPathitem) {
                foreach ($hiddenItems as $hiddenItem) {
                    if ($hiddenItem === $xPathitem) {
                        $link = $this->getSession()->getPage()->find('xpath', $xPathitem);
                        assert(null === $link);
                    }
                }
            }
        }
    }
}
