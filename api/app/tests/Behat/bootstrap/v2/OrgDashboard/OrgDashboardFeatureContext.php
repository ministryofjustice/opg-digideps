<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\OrgDashboard;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\ReportRepository;
use App\Tests\Behat\v2\ClientManagement\ClientManagementTrait;
use App\Tests\Behat\v2\Common\BaseFeatureContext;

class OrgDashboardFeatureContext extends BaseFeatureContext
{
    use ClientManagementTrait;

    // map from org names to the Organisation instances
    /** @var array<string, Organisation> */
    public array $orgs = [];

    // map from user emails to User instances
    /** @var array<string, User> */
    public array $users = [];

    public int $counter = 0;

    /**
     * @Given the organisation :name with email identifier :emailIdentifier exists
     */
    public function theOrganisationExists(string $name, string $emailIdentifier): void
    {
        $org = $this->fixtureHelper->createAndPersistOrganisation($name, $emailIdentifier);
        $this->orgs[$name] = $org;
    }

    /**
     * @Given a PA admin user with email :email exists
     *
     * Create and login as the PA admin with the given email
     */
    public function aPaManagerExists(string $email): void
    {
        $user = $this->fixtureHelper->createAndPersistUser(User::ROLE_PA_ADMIN, $email);
        $this->users[$email] = $user;
    }

    /**
     * @Given :email is in the :orgName organisation
     */
    public function userIsInOrganisation(string $email, string $orgName): void
    {
        $org = $this->orgs[$orgName];
        $user = $this->users[$email];
        $org->addUser($user);
        $this->em->persist($org);
        $this->em->flush();
    }

    /**
     * @Given there are :numReports reports which are :reportStatus associated with :orgName
     * @Given there is :numReports report which is :reportStatus associated with :orgName
     *
     * Set up one or more reports associated with an organisation
     */
    public function reportsAssociatedWithOrganisation(int $numReports, string $reportStatus, string $orgname): void
    {
        $org = $this->orgs[$orgname];

        /** @var ClientRepository $clientRepo */
        $clientRepo = $this->em->getRepository(Client::class);

        /** @var ReportRepository $reportRepo $i */
        $reportRepo = $this->em->getRepository(Report::class);

        for ($i = 1; $i <= $numReports; $i++) {
            $id = "$this->testRunId-$this->counter";
            $this->counter++;

            /** @var ?Report $report */
            $report = null;

            if ("notStarted" === $reportStatus) {
                // NB we don't want to retrieve the report and update its status, as it always updates to "notFinished"
                $userDetails = $this->fixtureHelper->createLayCombinedHighAssetsNotStarted($id);
            } elseif ("readyToSubmit" === $reportStatus) {
                $userDetails = $this->fixtureHelper->createLayCombinedHighAssetsCompleted($id);
                $report = $reportRepo->find($userDetails['currentReportId']);
            } elseif ("notFinished" === $reportStatus) {
                $userDetails = $this->fixtureHelper->createLayCombinedHighAssetsNotStarted($id);

                // we set one section so that the report status is "notFinished" and not "notStarted"
                $report = $reportRepo->find($userDetails['currentReportId']);
                $report->setActionMoreInfo('no');
            }

            if (!is_null($report)) {
                $report->updateSectionsStatusCache();
                $this->em->persist($report);
            }

            $clientId = $userDetails['clientId'];

            /** @var Client $client */
            $client = $clientRepo->find($clientId);

            $org->addClient($client);
            $client->setOrganisation($org);
        }

        $this->em->persist($org);
        $this->em->persist($client);
        $this->em->flush();
    }

    /**
     * @Given I press the search button
     */
    public function iSearchUsingTheFilter(): void
    {
        $this->findAllCssElements('#search_submit')[0]->click();
    }

    /**
     * @Given there should be :numReports reports on the org dashboard page
     * @Given there should be :numReports report on the org dashboard page
     *
     * Check reports shown on the /org dashboard page
     */
    public function thereShouldBeNReports(int $numReports): void
    {
        $rows = $this->findAllCssElements('.behat-region-client');

        $actualNumReports = count($rows);
        $this->assertIntEqualsInt($numReports, $actualNumReports, "expected $numReports reports, got $actualNumReports");
    }
}
