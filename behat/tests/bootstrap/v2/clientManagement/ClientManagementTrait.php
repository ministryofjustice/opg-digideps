<?php

declare(strict_types=1);

namespace DigidepsBehat\v2\ClientManagement;

use DateTime;
use DigidepsBehat\v2\Common\UserDetails;

trait ClientManagementTrait
{
    /**
     * @When I search for an existing client by their first name
     */
    public function iSearchForExistingClientByFirstName()
    {
        $user = is_null($this->interactingWithUserDetails) ? $this->profAdminDeputyNotStartedDetails : $this->interactingWithUserDetails;
        $this->searchForClientBy($user->getClientFirstName(), $user);
    }

    /**
     * @When I search for an existing client by their last name
     */
    public function iSearchForExistingClientByLastName()
    {
        $user = is_null($this->interactingWithUserDetails) ? $this->profAdminDeputyNotStartedDetails : $this->interactingWithUserDetails;
        $this->searchForClientBy($user->getClientLastName(), $user);
    }

    /**
     * @When I search for an existing client by their case number
     */
    public function iSearchForExistingClientByCaseNumber()
    {
        $user = is_null($this->interactingWithUserDetails) ? $this->profAdminDeputyNotStartedDetails : $this->interactingWithUserDetails;
        $this->searchForClientBy($user->getClientCaseNumber(), $user);
    }

    private function searchForClientBy(string $searchTerm, UserDetails $userDetailsInteractingWith)
    {
        $this->fillField('search_clients_q', $searchTerm);
        $this->pressButton('Search');

        $this->interactingWithUserDetails = $userDetailsInteractingWith;
    }

    /**
     * @Then I should see the clients details in the client list results
     */
    public function iShouldSeeClientDetailsInResults()
    {
        $this->iShouldSeeNClientsWithSameName(1);
    }

    /**
     * @Then I should see both the clients details in the client list results
     */
    public function iShouldSeeBothClientDetailsInResults()
    {
        $this->iShouldSeeNClientsWithSameName(2);
    }

    private function iShouldSeeNClientsWithSameName(int $numberClients)
    {
        $searchResultsDiv = $this->getSession()->getPage()->find('css', 'div.client-list');

        if (is_null($searchResultsDiv)) {
            $missingDivMessage = <<<MESSAGE
A div with the class client-list was not found.
This suggests one of the following:

- a search has not been completed on client search page
- the class of the search results div has been changed
MESSAGE;

            $this->throwContextualException($missingDivMessage);
        }

        $searchResultsHtml = $searchResultsDiv->getHtml();
        $fullClientName =sprintf(
            "%s %s",
            $this->interactingWithUserDetails->getClientFirstName(),
            $this->interactingWithUserDetails->getClientLastName()
        );

        $clientNameFound = substr_count($searchResultsHtml, $fullClientName);

        if ($clientNameFound < $numberClients) {
            $this->throwContextualException(
                sprintf(
                    'The client search results list did not contain the required occurrences of the clients full name. Expected: "%s" (at least %s times), got (full HTML): %s',
                    $fullClientName,
                    $numberClients,
                    $searchResultsHtml
                )
            );
        }
    }

    /**
     * @Then I should see the clients court order number
     */
    public function iShouldSeeCourtOrderNumber()
    {
        if (is_null($this->interactingWithUserDetails)) {
            $this->throwContextualException(
                'An interacting with User has not been set. Ensure a previous step in the scenario has set this User and try again.'
            );
        }

        $pageContent = $this->getSession()->getPage()->find('css', 'main#main-content')->getHtml();
        $courtOrderNumber = $this->interactingWithUserDetails->getCourtOrderNumber();
        $courtOrderNumberPresent = str_contains($pageContent, $courtOrderNumber);

        if (!$courtOrderNumberPresent) {
            $this->throwContextualException(
                sprintf(
                    'Expected court order number not found. Wanted: %s, got (full HTML): %s',
                    $courtOrderNumber,
                    $pageContent
                )
            );
        }
    }

    /**
     * @Then I should see the Lay deputies name, address and contact details
     */
    public function iShouldSeeLayDeputyDetails()
    {
        if (is_null($this->interactingWithUserDetails)) {
            $this->throwContextualException(
                '$interactingWithUserDetails has not been set. Ensure a previous step in the scenario has set this User and try again.'
            );
        }

        $pageContent = $this->getSession()->getPage()->find('css', 'main#main-content')->getHtml();

        $detailsToAssertOn[] = $this->interactingWithUserDetails->getUserFullname();
        $detailsToAssertOn[] = $this->interactingWithUserDetails->getUserPhone();
        $detailsToAssertOn[] = $this->interactingWithUserDetails->getUserEmail();
        $detailsToAssertOn = array_merge(
            $detailsToAssertOn,
            $this->interactingWithUserDetails->getUserFullAddressArray()
        );

        $missingDetails = [];

        foreach ($detailsToAssertOn as $detail) {
            $detailPresent = str_contains($pageContent, $detail);

            if (!$detailPresent) {
                $missingDetails[] = $detail;
            }
        }

        if (!empty($missingDetails)) {
            $missingDetailsString = implode(', ', $missingDetails);
            $detailsToAssertOnString = implode(', ', $detailsToAssertOn);

            $this->throwContextualException(
                sprintf(
                    'Some client details were missing: %s. Wanted: %s, got (full HTML): %s',
                    $missingDetailsString,
                    $detailsToAssertOnString,
                    $pageContent
                )
            );
        }
    }


    /**
     * @Then I should see the reports associated with the client
     */
    public function iShouldSeeDeputyReports()
    {
        if (is_null($this->interactingWithUserDetails)) {
            $this->throwContextualException(
                '$interactingWithUserDetails has not been set. Ensure a previous step in the scenario has set this User and try again.'
            );
        }

        $pageContent = $this->getSession()->getPage()->find('css', 'main#main-content')->getHtml();

        $currentReportDueDateVisible = str_contains($pageContent, $this->interactingWithUserDetails->getCurrentReportDueDate());

        if (!$currentReportDueDateVisible) {
            $this->throwContextualException(
                sprintf(
                    'Expected to find report with a due date of "%s" visible but it does not appear on the page. Got (full HTML): %s',
                    $this->interactingWithUserDetails->getCurrentReportDueDate(),
                    $pageContent
                )
            );
        }

        if (!is_null($this->interactingWithUserDetails->getPreviousReportDueDate())) {
            $previousReportDueDateVisible = str_contains($pageContent, $this->interactingWithUserDetails->getPreviousReportDueDate());

            if (!$previousReportDueDateVisible) {
                $this->throwContextualException(
                    sprintf(
                        'Expected to find report with a due date of "%s" visible but it does not appear on the page. Got (full HTML): %s',
                        $this->interactingWithUserDetails->getPreviousReportDueDate(),
                        $pageContent
                    )
                );
            }
        }
    }

    /**
     * @Then I should see the organisation the deputy belongs to
     */
    public function iShouldSeeDeputyOrganisation()
    {
        if (is_null($this->interactingWithUserDetails)) {
            $this->throwContextualException(
                '$interactingWithUserDetails has not been set. Ensure a previous step in the scenario has set this User and try again.'
            );
        }

        $xpathSelector = sprintf("//a[text() = '%s']", $this->interactingWithUserDetails->getOrganisationName());
        $linkHtml = $this->getSession()->getPage()->find('xpath', $xpathSelector)->getHtml();

        $orgNameLinkVisible = str_contains($linkHtml, $this->interactingWithUserDetails->getOrganisationName());

        if (!$orgNameLinkVisible) {
            $this->throwContextualException(
                sprintf(
                    'Expected to find a link with the text "%s" visible but it does not appear on the page. Got (full HTML): %s',
                    $this->interactingWithUserDetails->getCurrentReportDueDate(),
                    $this->getSession()->getPage()->find('css', 'main#main-content')->getHtml()
                )
            );
        }
    }

    /**
     * @Then I should see the name and email of the named deputy
     */
    public function iShouldSeeNamedDeputyNameAndEmail()
    {
        if (is_null($this->interactingWithUserDetails)) {
            $this->throwContextualException(
                '$interactingWithUserDetails has not been set. Ensure a previous step in the scenario has set this User and try again.'
            );
        }

        $namedDeputyName = $this->interactingWithUserDetails->getNamedDeputyName();
        $namedDeputyEmail = $this->interactingWithUserDetails->getNamedDeputyEmail();

        $nameXpathSelector = "//dt[normalize-space() = 'Named deputy']/..";
        $namedDeputyNameDivHtml = $this->getSession()->getPage()->find('xpath', $nameXpathSelector)->getHtml();

        $namedDeputyNameVisible = str_contains($namedDeputyNameDivHtml, $namedDeputyName);

        $emailXpathSelector = "//h3[normalize-space() = 'Named deputy contact details']/..";
        $namedDeputyNameDivHtml = $this->getSession()->getPage()->find('xpath', $emailXpathSelector)->getHtml();

        $namedDeputyEmailVisible = str_contains($namedDeputyNameDivHtml, $namedDeputyEmail);

        if (!$namedDeputyNameVisible || !$namedDeputyEmailVisible) {
            $this->throwContextualException(
                sprintf(
                    'Expected to find the named deputy details (Name: "%s", Email: "%s") but they do not appear on the page. Got (full HTML): %s',
                    $namedDeputyName,
                    $namedDeputyEmail,
                    $this->getSession()->getPage()->find('css', 'main#main-content')->getHtml()
                )
            );
        }
    }

    /**
     * @When I attempt to discharge the client
     */
    public function iAttemptToDischargeTheClient()
    {
        if (is_null($this->interactingWithUserDetails)) {
            $this->throwContextualException(
                '$interactingWithUserDetails has not been set. Ensure a previous step in the scenario has set this User and try again.'
            );
        }

        try {
            $this->clickLink('Discharge deputy');
            $this->iAmOnAdminClientDischargePage();
            $this->clickLink('Discharge deputy');
        } catch (\Throwable $e) {
            // This step is used as part of testing the discharge button isnt here so swallow errors and assert on following step
        }
    }

    /**
     * @Then the client should be discharged
     */
    public function theClientShouldBeDischarged()
    {
        if (is_null($this->interactingWithUserDetails)) {
            $this->throwContextualException(
                '$interactingWithUserDetails has not been set. Ensure a previous step in the scenario has set this User and try again.'
            );
        }

        $this->iVisitLayClientDetailsPage();

        $dischargedOnSelector = "//dt[normalize-space() = 'Discharged on']/..";
        $clientDtHtml = $this->getSession()->getPage()->find('xpath', $dischargedOnSelector)->getHtml();
        $todayString = (new DateTime())->format('j M Y');

        $clientIsDischarged = str_contains($clientDtHtml, $todayString);

        if (!$clientIsDischarged) {
            $this->throwContextualException(
                sprintf('The client does not appear to be discharged. Expected: %s, got (HTML of discharged dt): %s', $todayString, $clientDtHtml)
            );
        }
    }

    /**
     * @Then the client should not be discharged
     */
    public function theClientShouldNotBeDischarged()
    {
        if (is_null($this->interactingWithUserDetails)) {
            $this->throwContextualException(
                '$interactingWithUserDetails has not been set. Ensure a previous step in the scenario has set this User and try again.'
            );
        }

        $this->iVisitLayClientDetailsPage();

        $dischargedOnSelector = "//dt[normalize-space() = 'Discharged on']/..";
        $dischargedOnVisible = $this->getSession()->getPage()->find('xpath', $dischargedOnSelector);

        if (!is_null($dischargedOnVisible)) {
            $this->throwContextualException(
                sprintf('The client does not appear to be discharged. Expected: %s, got (HTML of discharged dt): %s', $todayString, $clientDtHtml)
            );
        }
    }
}
