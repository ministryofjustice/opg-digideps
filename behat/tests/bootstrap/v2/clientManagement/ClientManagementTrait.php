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
        $this->searchForClientBy(
            $this->profAdminDeputyNotStartedDetails->getClientFirstName(),
            $this->profAdminDeputyNotStartedDetails
        );
    }

    /**
     * @When I search for an existing client by their last name
     */
    public function iSearchForExistingClientByLastName()
    {
        $this->searchForClientBy(
            $this->profAdminDeputyNotStartedDetails->getClientLastName(),
            $this->profAdminDeputyNotStartedDetails
        );
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

        var_dump($this->interactingWithUserDetails->getClientFirstName());
        var_dump($this->interactingWithUserDetails->getClientLastName());
        var_dump($fullClientName);

        $clientNameFound = str_contains($searchResultsHtml, $fullClientName);

        if (!$clientNameFound) {
            $this->throwContextualException(
                sprintf(
                    'The client search results list did not contain the clients full name. Expected: "%s", got (full HTML): %s',
                    $fullClientName,
                    $searchResultsHtml
                )
            );
        }
    }
}
