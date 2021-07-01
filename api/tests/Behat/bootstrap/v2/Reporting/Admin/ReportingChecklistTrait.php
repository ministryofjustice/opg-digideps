<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Reporting\Admin;

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

    /**
     * @When I navigate to the clients search page
     */
    public function iNavigateToTheClientsSearchPage()
    {
        $this->iVisitAdminClientSearchPage();
        $this->iAmOnAdminClientsSearchPage();
    }

    /**
     * @When I search for the client
     */
    public function iSearchForTheClient()
    {
        $user = is_null($this->interactingWithUserDetails) ? $this->layDeputySubmittedHealthWelfareDetails : $this->interactingWithUserDetails;
        $this->searchAdminForClientWithTerm($user->getClientCaseNumber());
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
        $this->selectOption('report_checklist[reportingPeriodAccurate]', 'yes');
        $this->checkOption('report_checklist[contactDetailsUptoDate]');
        $this->checkOption('report_checklist[deputyFullNameAccurateInCasrec]');
        $this->selectOption('report_checklist[decisionsSatisfactory]', 'yes');
        $this->selectOption('report_checklist[consultationsSatisfactory]', 'yes');
        $this->selectOption('report_checklist[careArrangements]', 'yes');
        $this->selectOption('report_checklist[satisfiedWithHealthAndLifestyle]', 'yes');
        $this->selectOption('report_checklist[futureSignificantDecisions]', 'yes');
        $this->selectOption('report_checklist[hasDeputyRaisedConcerns]', 'yes');
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
}
