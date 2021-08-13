<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\ClientManagement;

use App\Tests\Behat\BehatException;
use App\Tests\Behat\v2\Common\UserDetails;
use DateTime;

trait ClientManagementTrait
{
    private ?int $clientCount = null;

    /**
     * @When I search for an existing client by their first name
     */
    public function iSearchForExistingClientByFirstName()
    {
        $user = is_null($this->interactingWithUserDetails) ? $this->profAdminDeputyHealthWelfareNotStartedDetails : $this->interactingWithUserDetails;
        $this->searchForClientBy($user->getClientFirstName(), $user);
    }

    /**
     * @When I search for an existing client by their last name
     */
    public function iSearchForExistingClientByLastName()
    {
        $user = is_null($this->interactingWithUserDetails) ? $this->profAdminDeputyHealthWelfareNotStartedDetails : $this->interactingWithUserDetails;
        $this->searchForClientBy($user->getClientLastName(), $user);
    }

    /**
     * @When I search for an existing client by their full name
     */
    public function iSearchForExistingClientByFullName()
    {
        $user = is_null($this->interactingWithUserDetails) ? $this->profAdminDeputyHealthWelfareNotStartedDetails : $this->interactingWithUserDetails;
        $fullName = sprintf('%s %s', $user->getClientFirstName(), $user->getClientLastName());
        $this->searchForClientBy($fullName, $user);
    }

    /**
     * @When I search for an existing client by their case number
     */
    public function iSearchForExistingClientByCaseNumber()
    {
        $user = is_null($this->interactingWithUserDetails) ? $this->profAdminDeputyHealthWelfareNotStartedDetails : $this->interactingWithUserDetails;
        $this->searchForClientBy($user->getClientCaseNumber(), $user);
    }

    /**
     * @When I search for an non-existent client
     */
    public function iSearchForNonExistentClient()
    {
        $user = $this->adminDetails;
        $this->searchForClientBy('Björk Guðmundsdóttir', $user);
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
        $this->clientCount = 1;
        $this->iShouldSeeNClientsWithSameName();
    }

    /**
     * @Then I should see both the clients details in the client list results
     */
    public function iShouldSeeBothClientDetailsInResults()
    {
        $this->clientCount = 2;
        $this->iShouldSeeNClientsWithSameName();
    }

    private function iShouldSeeNClientsWithSameName()
    {
        $this->assertClientCountSet();

        $searchResultsHtml = $this->getSearchResultHtml();

        $fullClientName = sprintf(
            '%s %s',
            $this->interactingWithUserDetails->getClientFirstName(),
            $this->interactingWithUserDetails->getClientLastName()
        );

        $clientNameFoundCount = substr_count($searchResultsHtml, $fullClientName);

        if ($clientNameFoundCount < $this->clientCount) {
            throw new BehatException(sprintf('The client search results list did not contain the required occurrences of the clients full name. Expected: "%s" (at least %s times), got (full HTML): %s', $fullClientName, $this->clientCount, $searchResultsHtml));
        }
    }

    /**
     * @Then I should see the correct count of clients in the client list results
     */
    public function iShouldSeeCorrectCountOfClients()
    {
        $this->assertClientCountSet();

        $searchResultsHtml = $this->getSearchResultHtml();

        $searchString = $this->clientCount > 1 ? sprintf('Found %d clients', $this->clientCount) : 'Found 1 client';
        $foundNClients = str_contains($searchResultsHtml, $searchString);

        if (!$foundNClients) {
            throw new BehatException(sprintf('The client search results list did not count the correct number of clients found. Expected: "%s", got (full HTML): %s', $searchString, $searchResultsHtml));
        }
    }

    private function assertClientCountSet()
    {
        if (is_null($this->clientCount)) {
            throw new BehatException(sprintf("You're attempting to run a step definition that requires this->clientCount to be set but its null. Set it and try again."));
        }
    }

    /**
     * @Then I should see No Clients Found in the client list results
     */
    public function iShouldSeeNoClientsFound()
    {
        $searchResultsHtml = $this->getSearchResultHtml();
        $noClientsFound = str_contains($searchResultsHtml, 'No clients found');

        if (!$noClientsFound) {
            throw new BehatException(sprintf('The client search results list did not display "No clients found". Expected: "No clients found", got (full HTML): %s', $searchResultsHtml));
        }
    }

    /**
     * @return mixed
     */
    private function getSearchResultHtml()
    {
        $searchResultsDiv = $this->getSession()->getPage()->find('css', 'div.client-list');

        if (is_null($searchResultsDiv)) {
            $missingDivMessage = <<<MESSAGE
A div with the class client-list was not found.
This suggests one of the following:

- a search has not been completed on client search page
- the class of the search results div has been changed
MESSAGE;

            throw new BehatException($missingDivMessage);
        }

        return $searchResultsDiv->getHtml();
    }

    /**
     * @Then I should see the clients court order number
     */
    public function iShouldSeeCourtOrderNumber()
    {
        $this->assertInteractingWithUserIsSet();

        $pageContent = $this->getSession()->getPage()->find('css', 'main#main-content')->getHtml();
        $courtOrderNumber = $this->interactingWithUserDetails->getCourtOrderNumber();
        $courtOrderNumberPresent = str_contains($pageContent, $courtOrderNumber);

        if (!$courtOrderNumberPresent) {
            throw new BehatException(sprintf('Expected court order number not found. Wanted: %s, got (full HTML): %s', $courtOrderNumber, $pageContent));
        }
    }

    /**
     * @Then I should see the Lay deputies name, address and contact details
     */
    public function iShouldSeeLayDeputyDetails()
    {
        $this->assertInteractingWithUserIsSet();

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

            throw new BehatException(sprintf('Some client details were missing: %s. Wanted: %s, got (full HTML): %s', $missingDetailsString, $detailsToAssertOnString, $pageContent));
        }
    }

    /**
     * @Then I should see the reports associated with the client
     */
    public function iShouldSeeDeputyReports()
    {
        $this->assertInteractingWithUserIsSet();

        $pageContent = $this->getSession()->getPage()->find('css', 'main#main-content')->getHtml();

        $currentReportDateString = $this->interactingWithUserDetails->getCurrentReportDueDate()->format('j F Y');
        $currentReportDueDateVisible = str_contains($pageContent, $currentReportDateString);

        if (!$currentReportDueDateVisible) {
            throw new BehatException(sprintf('Expected to find report with a due date of "%s" visible but it does not appear on the page. Got (full HTML): %s', $currentReportDateString, $pageContent));
        }

        if (!is_null($this->interactingWithUserDetails->getPreviousReportDueDate())) {
            $previousReportDateString = $this->interactingWithUserDetails->getPreviousReportDueDate()->format('j F Y');
            $previousReportDueDateVisible = str_contains($pageContent, $previousReportDateString);

            if (!$previousReportDueDateVisible) {
                throw new BehatException(sprintf('Expected to find report with a due date of "%s" visible but it does not appear on the page. Got (full HTML): %s', $previousReportDateString, $pageContent));
            }
        }
    }

    /**
     * @Then I should see the organisation the deputy belongs to
     */
    public function iShouldSeeDeputyOrganisation()
    {
        $this->assertInteractingWithUserIsSet();

        $xpathSelector = sprintf("//a[text() = '%s']", $this->interactingWithUserDetails->getOrganisationName());

        $linkHtml = $this->getSession()->getPage()->find('xpath', $xpathSelector)->getHtml();

        $orgNameLinkVisible = str_contains($linkHtml, $this->interactingWithUserDetails->getOrganisationName());

        if (!$orgNameLinkVisible) {
            throw new BehatException(sprintf('Expected to find a link with the text "%s" visible but it does not appear on the page. Got (full HTML): %s', $this->interactingWithUserDetails->getCurrentReportDueDate(), $this->getSession()->getPage()->find('css', 'main#main-content')->getHtml()));
        }
    }

    /**
     * @Then I should see the name and email of the named deputy
     */
    public function iShouldSeeNamedDeputyNameAndEmail()
    {
        $this->assertInteractingWithUserIsSet();

        $namedDeputyName = $this->interactingWithUserDetails->getNamedDeputyName();
        $namedDeputyEmail = $this->interactingWithUserDetails->getNamedDeputyEmail();

        $nameXpathSelector = "//dt[normalize-space() = 'Named deputy']/..";
        $namedDeputyNameDivHtml = $this->getSession()->getPage()->find('xpath', $nameXpathSelector)->getHtml();

        $namedDeputyNameVisible = str_contains($namedDeputyNameDivHtml, $namedDeputyName);

        $emailXpathSelector = "//h3[normalize-space() = 'Named deputy contact details']/..";
        $namedDeputyNameDivHtml = $this->getSession()->getPage()->find('xpath', $emailXpathSelector)->getHtml();

        $namedDeputyEmailVisible = str_contains($namedDeputyNameDivHtml, $namedDeputyEmail);

        if (!$namedDeputyNameVisible || !$namedDeputyEmailVisible) {
            throw new BehatException(sprintf('Expected to find the named deputy details (Name: "%s", Email: "%s") but they do not appear on the page. Got (full HTML): %s', $namedDeputyName, $namedDeputyEmail, $this->getSession()->getPage()->find('css', 'main#main-content')->getHtml()));
        }
    }

    /**
     * @When I attempt to discharge the client
     */
    public function iAttemptToDischargeTheClient()
    {
        $this->assertInteractingWithUserIsSet();

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
        $this->assertInteractingWithUserIsSet();

        $this->iVisitAdminLayClientDetailsPage();

        $dischargedOnSelector = "//dt[normalize-space() = 'Discharged on']/..";
        $clientDtHtml = $this->getSession()->getPage()->find('xpath', $dischargedOnSelector)->getHtml();
        $todayString = (new DateTime())->format('j M Y');

        $clientIsDischarged = str_contains($clientDtHtml, $todayString);

        if (!$clientIsDischarged) {
            throw new BehatException(sprintf('The client does not appear to be discharged. Expected: %s, got (HTML of discharged dt): %s', $todayString, $clientDtHtml));
        }
    }

    /**
     * @Then the client should not be discharged
     */
    public function theClientShouldNotBeDischarged()
    {
        $this->assertInteractingWithUserIsSet();

        $this->iVisitAdminLayClientDetailsPage();

        $dischargedOnSelector = "//dt[normalize-space() = 'Discharged on']/..";
        $dischargedOnVisible = $this->getSession()->getPage()->find('xpath', $dischargedOnSelector);

        if (!is_null($dischargedOnVisible)) {
            $clientDtHtml = $this->getSession()->getPage()->find('xpath', $dischargedOnSelector)->getHtml();

            throw new BehatException(sprintf('The client appears to be discharged. Expected "Discharged on" not to appear, got (HTML of discharged dt): %s', $clientDtHtml));
        }
    }
}
