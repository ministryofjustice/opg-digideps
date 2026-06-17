<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\ClientManagement;

use Behat\Mink\Element\NodeElement;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Organisation;
use Tests\OPG\Digideps\Backend\Behat\BehatException;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\UserDetails;

trait ClientManagementTrait
{
    private ?int $clientCount = null;
    private string $organisationEmail = '';
    private int $dischargedClient = 0;

    /**
     * @When I search for an existing client by their first name
     */
    public function iSearchForExistingClientByFirstName(): void
    {
        $user = is_null($this->interactingWithUserDetails) ? $this->profAdminDeputyHealthWelfareNotStartedDetails : $this->interactingWithUserDetails;
        $this->searchForClientBy($user->getClientFirstName(), $user);
    }

    /**
     * @When I search for an existing client by their last name
     */
    public function iSearchForExistingClientByLastName(): void
    {
        $user = is_null($this->interactingWithUserDetails) ? $this->profAdminDeputyHealthWelfareNotStartedDetails : $this->interactingWithUserDetails;
        $this->searchForClientBy($user->getClientLastName(), $user);
    }

    /**
     * @When I search for an existing client by their full name
     */
    public function iSearchForExistingClientByFullName(): void
    {
        $user = is_null($this->interactingWithUserDetails) ? $this->profAdminDeputyHealthWelfareNotStartedDetails : $this->interactingWithUserDetails;
        $fullName = sprintf('%s %s', $user->getClientFirstName(), $user->getClientLastName());
        $this->searchForClientBy($fullName, $user);
    }

    /**
     * @When I search for an existing client by their case number
     */
    public function iSearchForExistingClientByCaseNumber(): void
    {
        $user = is_null($this->interactingWithUserDetails) ? $this->profAdminDeputyHealthWelfareNotStartedDetails : $this->interactingWithUserDetails;
        $this->searchForClientBy($user->getClientCaseNumber(), $user);
    }

    /**
     * @When I search for an non-existent client
     */
    public function iSearchForNonExistentClient(): void
    {
        $user = $this->adminDetails;
        $this->searchForClientBy('Björk Guðmundsdóttir', $user);
    }

    private function searchForClientBy(string $searchTerm, UserDetails $userDetailsInteractingWith): void
    {
        $this->fillField('search_clients_q', $searchTerm);
        $this->pressButton('Search');

        $this->interactingWithUserDetails = $userDetailsInteractingWith;
    }

    /**
     * @Then I should see the clients details in the client list results
     */
    public function iShouldSeeClientDetailsInResults(): void
    {
        $this->clientCount = 1;
        $this->iShouldSeeNClientsWithSameName('full');
    }

    /**
     * @Then I should see :occurances clients details in the client list results with the same :whichName name
     */
    public function iShouldSeeBothClientDetailsInResults(int $occurances, string $whichname): void
    {
        $this->clientCount = $occurances;
        $this->iShouldSeeNClientsWithSameName($whichname);
    }

    private function iShouldSeeNClientsWithSameName(string $whichName): void
    {
        $this->assertClientCountSet();

        $searchResultsHtml = $this->getSearchResultHtml();

        switch (strtolower($whichName)) {
            case 'first':
                $searchName = $this->interactingWithUserDetails->getClientFirstName();
                break;
            case 'last':
                $searchName = $this->interactingWithUserDetails->getClientLastName();
                break;
            case 'full':
                $searchName = sprintf(
                    '%s %s',
                    $this->interactingWithUserDetails->getClientFirstName(),
                    $this->interactingWithUserDetails->getClientLastName()
                );
                break;
            default:
                throw new BehatException('This step only supports "first|last|full" as a search term. Either update step argument or add a case statement.');
        }

        $clientNameFoundCount = substr_count($searchResultsHtml, $searchName);

        if ($clientNameFoundCount < $this->clientCount) {
            throw new BehatException(sprintf('The client search results list did not contain the required occurrences of the clients full name. Expected: "%s" (at least %s times), got (full HTML): %s', $searchName, $this->clientCount, $searchResultsHtml));
        }
    }

    /**
     * @Then I should see the correct count of clients in the client list results
     */
    public function iShouldSeeCorrectCountOfClients(): void
    {
        $this->assertClientCountSet();

        $searchResultsHtml = $this->getSearchResultHtml();

        $searchString = $this->clientCount > 1 ? sprintf('Found %d clients', $this->clientCount) : 'Found 1 client';
        $foundNClients = str_contains($searchResultsHtml, $searchString);

        if (!$foundNClients) {
            throw new BehatException(sprintf('The client search results list did not count the correct number of clients found. Expected: "%s", got (full HTML): %s', $searchString, $searchResultsHtml));
        }
    }

    private function assertClientCountSet(): void
    {
        if (is_null($this->clientCount)) {
            throw new BehatException(sprintf("You're attempting to run a step definition that requires this->clientCount to be set but its null. Set it and try again."));
        }
    }

    /**
     * @Then I should see No Clients Found in the client list results
     */
    public function iShouldSeeNoClientsFound(): void
    {
        $searchResultsHtml = $this->getSearchResultHtml();
        $noClientsFound = str_contains($searchResultsHtml, 'No clients found');

        if (!$noClientsFound) {
            throw new BehatException(sprintf('The client search results list did not display "No clients found". Expected: "No clients found", got (full HTML): %s', $searchResultsHtml));
        }
    }

    private function getSearchResultHtml(): string
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

        return $this->safeGetHtml($searchResultsDiv);
    }

    /**
     * @Then I should see the clients court order number
     */
    public function iShouldSeeCourtOrderNumber(): void
    {
        $this->assertInteractingWithUserIsSet();

        $pageContent = $this->safeGetHtml($this->getSession()->getPage()->find('css', 'main#main-content'));
        $caseNumber = $this->interactingWithUserDetails?->getClientCaseNumber() ?? '';
        $caseNumberPresent = str_contains($pageContent, $caseNumber);

        if (!$caseNumberPresent) {
            throw new BehatException(sprintf('Expected court order number not found. Wanted: %s, got (full HTML): %s', $caseNumber, $pageContent));
        }
    }

    /**
     * @Then I should see the Lay deputies name, address and contact details
     */
    public function iShouldSeeLayDeputyDetails(): void
    {
        $this->assertInteractingWithUserIsSet();

        $pageContent = $this->safeGetHtml($this->getSession()->getPage()->find('css', 'main#main-content'));

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
     * @Then I should see the Primary Lay deputies name, address and contact details
     */
    public function iShouldSeePrimaryLayDeputyDetails(): void
    {
        $pageContent = $this->safeGetHtml($this->getSession()->getPage()->find('css', 'main#main-content'));

        $detailsToAssertOn[] = $this->layPfaHighNotStartedMultiClientDeputyPrimaryUser->getUserFullname();
        $detailsToAssertOn[] = $this->layPfaHighNotStartedMultiClientDeputyPrimaryUser->getUserPhone();
        $detailsToAssertOn[] = $this->layPfaHighNotStartedMultiClientDeputyPrimaryUser->getUserEmail();
        $detailsToAssertOn = array_merge(
            $detailsToAssertOn,
            $this->layPfaHighNotStartedMultiClientDeputyPrimaryUser->getUserFullAddressArray()
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
    public function iShouldSeeDeputyReports(): void
    {
        $this->assertInteractingWithUserIsSet();

        $pageContent = $this->safeGetHtml($this->getSession()->getPage()->find('css', 'main#main-content'));

        $currentReportDateString = $this->interactingWithUserDetails->getCurrentReportDueDate()?->format('j F Y') ?? '';
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
    public function iShouldSeeDeputyOrganisation(): void
    {
        $this->assertInteractingWithUserIsSet();

        $xpathSelector = sprintf("//a[text() = '%s']", $this->interactingWithUserDetails->getOrganisationName());

        $linkHtml = $this->safeGetHtml($this->getSession()->getPage()->find('xpath', $xpathSelector));

        $orgNameLinkVisible = str_contains($linkHtml, $this->interactingWithUserDetails->getOrganisationName());

        if (!$orgNameLinkVisible) {
            throw new BehatException(
                sprintf(
                    'Expected to find a link with the text "%s" visible but it does not appear on the page. Got (full HTML): %s',
                    $this->interactingWithUserDetails->getCurrentReportDueDate()?->format('Y-m-d') ?? '',
                    $this->safeGetHtml($this->getSession()->getPage()->find('css', 'main#main-content'))
                )
            );
        }
    }

    /**
     * @Then I should see the name and email of the named deputy
     */
    public function iShouldSeeNamedDeputyNameAndEmail(): void
    {
        $this->assertInteractingWithUserIsSet();

        $deputyName = $this->interactingWithUserDetails->getDeputyName();
        $deputyEmail = $this->interactingWithUserDetails->getDeputyEmail();

        $nameXpathSelector = "//dt[normalize-space() = 'Named deputy']/..";
        $namedDeputyNameDivHtml = $this->safeGetHtml($this->getSession()->getPage()->find('xpath', $nameXpathSelector));

        $namedDeputyNameVisible = str_contains($namedDeputyNameDivHtml, $deputyName);

        $emailXpathSelector = "//h3[normalize-space() = 'Named deputy contact details']/..";
        $namedDeputyNameDivHtml = $this->safeGetHtml($this->getSession()->getPage()->find('xpath', $emailXpathSelector));

        $namedDeputyEmailVisible = str_contains($namedDeputyNameDivHtml, $deputyEmail);

        if (!$namedDeputyNameVisible || !$namedDeputyEmailVisible) {
            throw new BehatException(
                sprintf(
                    'Expected to find the named deputy details (Name: "%s", Email: "%s") but they do not appear on ' .
                        'the page. Got (full HTML): %s',
                    $deputyName,
                    $deputyEmail,
                    $this->safeGetHtml($this->getSession()->getPage()->find('css', 'main#main-content'))
                )
            );
        }
    }

    /**
     * @When I attempt to discharge the client
     */
    public function iAttemptToDischargeTheClient(): void
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
    public function theClientShouldBeDischarged(): void
    {
        $this->assertInteractingWithUserIsSet();

        $this->iVisitAdminClientDetailsPageForDeputyInteractingWith();

        if ($this->interactingWithUserDetails->getOrganisationName() != null) {
            $this->organisationEmail = $this->interactingWithUserDetails->getOrganisationEmailIdentifier();
        }

        $dischargedOnSelector = "//dt[normalize-space() = 'Discharged on']/..";
        $clientDt = $this->getSession()->getPage()->find('xpath', $dischargedOnSelector);

        if (!$clientDt) {
            throw new BehatException('Could not find a dt element on the page with text "Discharged on".');
        }

        $clientDtHtml = $this->safeGetHtml($clientDt);
        $todayString = new \DateTime()->format('j M Y');

        $clientIsDischarged = str_contains($clientDtHtml, $todayString);
        ++$this->dischargedClient;

        if (!$clientIsDischarged) {
            throw new BehatException(
                sprintf(
                    'The client does not appear to be discharged. Expected: %s, got (HTML of discharged dt): %s',
                    $todayString,
                    $clientDtHtml
                )
            );
        }
    }

    /**
     * @Then the client should not be discharged
     */
    public function theClientShouldNotBeDischarged(): void
    {
        $this->assertInteractingWithUserIsSet();

        $this->iVisitAdminClientDetailsPageForDeputyInteractingWith();

        $dischargedOnSelector = "//dt[normalize-space() = 'Discharged on']/..";
        $dischargedOnVisible = $this->getSession()->getPage()->find('xpath', $dischargedOnSelector);

        if (!is_null($dischargedOnVisible)) {
            $clientDtHtml = $this->safeGetHtml($this->getSession()->getPage()->find('xpath', $dischargedOnSelector));

            throw new BehatException(sprintf('The client appears to be discharged. Expected "Discharged on" not to appear, got (HTML of discharged dt): %s', $clientDtHtml));
        }
    }

    /**
     * @Given /^the client does not have a named deputy associated with them$/
     */
    public function theClientDoesNotHaveANamedDeputyAssociatedWithThem(): void
    {
        $this->assertInteractingWithUserIsSet();

        $client = $this->em->find(Client::class, $this->interactingWithUserDetails->getClientId());
        $client->setDeputy(null);

        $this->em->persist($client);
        $this->em->flush();
    }

    /**
     * @When I attempt to un-archive the client
     */
    public function iAttemptToUnarchiveTheClient(): void
    {
        $this->assertInteractingWithUserIsSet();

        try {
            $this->clickLink('Un-archive client');
            $this->iAmOnPage('/admin\/client\/.*\/unarchive.*$/');
            $this->clickLink('Return to client dashboard');
        } catch (\Throwable $e) {
            // This step is used as part of testing the unarchive button isnt here so swallow errors and assert on following step
        }
    }

    /**
     * @When an org deputy has an archived client
     */
    public function theDeputyHasAnArchivedClient(): void
    {
        $this->assertInteractingWithUserIsSet();
        $clientId = $this->interactingWithUserDetails->getClientId();

        /** @var Client $client */
        $client = $this->em->getRepository(Client::class)->find($clientId);

        $client->setArchivedAt(new \DateTime('yesterday'));

        $this->em->persist($client);
        $this->em->flush();
    }

    /**
     * @Then the client should be unarchived
     */
    public function theClientShouldBeUnarchived(): void
    {
        $this->iAmOnAdminClientDetailsPage();
    }

    /**
     * @Then the client should not be unarchived
     */
    public function theClientShouldNotBeUnarchived(): void
    {
        $this->iVisitAdminClientDetailsPageForDeputyInteractingWith();

        // Expecting to be redirected to the client archived page
        $this->iAmOnPage('/admin\/client\/.*\/archived.*$/');
    }

    /**
     * @Given /^the deputy I am interacting with has been discharged$/
     */
    public function theDeputyHasBeenDischarged(): void
    {
        $this->assertInteractingWithUserIsSet();

        $client = $this->em->find(Client::class, $this->interactingWithUserDetails->getClientId());

        // NB discharging a deputy is the same as soft-deleting them (sets deletedAt to datetime)
        $client->setDeletedAt(new \DateTime('yesterday'));
        $this->em->persist($client);
        $this->em->flush();

        // necessary to ensure that the next time this client is referenced, it is re-fetched from the db
        $this->em->detach($client);
    }

    /**
     * @Given /^I select the first client from the Choose a Client page$/
     */
    public function iSelectTheFirstClient(): void
    {
        $firstClientId = $this->loggedInUserDetails->getClientId();
        $this->visitPath('/client/' . $firstClientId);
    }

    /**
     * @When /^I select the "([^"]*)" link I should see the details of the chosen client$/
     */
    public function iSelectTheLinkIShouldSeeTheDetailsOfTheChosenClient($link): void
    {
        $this->clickLink($link);

        $firstClientId = $this->loggedInUserDetails->getClientId();
        $client = $this->em->find(Client::class, $firstClientId);

        $this->assertPageContainsText(sprintf('%s\'s details', $client->getFirstname()));
        $this->assertPageContainsText($client->getCaseNumber());
    }

    /**
     * @Given /^I click on the button to edit my client's details$/
     */
    public function IClickOnTheButtonToEditMyClientsDetails(): void
    {
        $clientName = $this->loggedInUserDetails->getClientFirstName();
        $this->clickLink(sprintf('Edit %s\'s details', $clientName));
    }

    /**
     * @Then I click the save button
     */
    public function iClickSaveButton(): void
    {
        $this->pressButton('Save');
    }

    /**
     * @Then I should see validation errors for address and postcode fields
     */
    public function iSeeValidationErrorsForClientEditPage(): void
    {
        $this->assertPageContainsText('Enter the client\'s address');
        $this->assertPageContainsText('Enter the client\'s postcode');
    }

    /**
     * @Then I should not see the validation error for the court order date
     */
    public function iShouldNotSeeValidationErrorsForCourtOrderDate(): void
    {
        $this->assertPageNotContainsText('Enter the date of your court order');
    }

    /**
     * @Given /^I can see a count of active and discharged clients on the organisations page$/
     */
    public function iCanSeeTheActiveAndDishargedClientCountOnTheOrganisationsPage(): void
    {
        $this->visitAdminPath('/admin/organisations');

        $organisation = $this->em->getRepository(Organisation::class)->findOneBy(['emailIdentifier' => $this->organisationEmail]);

        $this->clickLink($organisation->getName());

        $this->iAmOnAdminOrganisationOverviewPage();

        $activeClients = $this->em->getRepository(Client::class)->findBy(['organisation' => $organisation->getId()]);

        $xpath = "//div[contains(@class, 'govuk-summary-list__row')]";
        $listSummaryRowItems = $this->getSession()->getPage()->findAll('xpath', $xpath);

        $rows = [];
        foreach ($listSummaryRowItems as $row) {
            $rows[] = strtolower($row->getText());
        }

        $this->assertStringEqualsString(sprintf('clients %s', count($activeClients)), $rows[2], 'Asserting active and archived client count found on page');

        $this->assertStringEqualsString("discharged clients $this->dischargedClient", $rows[3], 'Asserting discharged client count found on page');
    }

    private function safeGetHtml(mixed $possibleElement): string
    {
        if (!($possibleElement instanceof NodeElement)) {
            return '';
        }

        return $possibleElement->getHtml();
    }
}
