<?php declare(strict_types=1);


namespace DigidepsBehat\v2\ClientManagement;

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
}
