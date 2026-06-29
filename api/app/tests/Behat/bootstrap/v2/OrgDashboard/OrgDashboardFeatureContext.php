<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Behat\v2\OrgDashboard;

use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\OrganisationRepository;
use Tests\OPG\Digideps\Backend\Behat\v2\ClientManagement\ClientManagementTrait;
use Tests\OPG\Digideps\Backend\Behat\v2\Common\BaseFeatureContext;
use Tests\OPG\Digideps\Backend\Behat\v2\ReportSubmission\ReportSubmissionTrait;

class OrgDashboardFeatureContext extends BaseFeatureContext
{
    use ClientManagementTrait;
    use ReportSubmissionTrait;

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
     * Set up one or more reports associated with an organisation, creating a named deputy if necessary.
     *
     * $reportStatus = one of "notStarted", "notFinished", "readyToSubmit"
     */
    public function reportsAssociatedWithOrganisation(int $numReports, string $reportStatus, string $orgname): void
    {
        /** @var OrganisationRepository $orgRepository */
        $orgRepository = $this->em->getRepository(Organisation::class);

        // find org by name (assumption: it's been created by previous behat steps)
        /** @var Organisation $org */
        $org = $orgRepository->findOneBy(['name' => $orgname]);
        if ($org === null) {
            throw new \RuntimeException("Organisation with name $orgname was not found");
        }

        for ($i = 1; $i <= $numReports; $i++) {
            $id = "$this->testRunId-$this->counter";
            $this->counter++;

            // create client on org
            $client = $this->fixtureHelper->generateClient(org: $org);
            $this->em->persist($client);

            // create court order on client
            $courtOrder = $this->fixtureHelper->createAndPersistCourtOrder(CourtOrderType::PFA, $client);

            // create named deputy in org, associate with court order, and add to org
            // (NB we don't create a user for any of these deputies as they are named deputies)
            $deputyEmail = "org-named-deputy-" . $id . '@' . $org->getEmailIdentifier();
            $deputy = $this->fixtureHelper->createDeputy($deputyEmail);
            $deputy->associateWithCourtOrder($courtOrder);
            $deputy->setOrganisation($org);
            $this->em->persist($deputy);

            // create report on client
            $report = $this->reportTestHelper->generateReport($this->em, $client, '102', dateChecks: false);

            if ($reportStatus === "notFinished") {
                // complete one section so that the report status is "notFinished" and *not* "notStarted"
                $report->setActionMoreInfo('no');
            } elseif ($reportStatus === "readyToSubmit") {
                // complete the whole report
                $this->reportTestHelper->completeReport($report, $this->em);
            } elseif ($reportStatus !== "notStarted") {
                throw new \LogicException("invalid report status: $reportStatus");
            }

            // don't update the report status if "notStarted", as it always updates to "notFinished"
            // even though no sections have been completed
            if ($reportStatus !== "notStarted") {
                $report->updateSectionsStatusCache();
            }

            $this->em->persist($report);

            // associate report with court order
            $courtOrder->addReport($report);
            $this->em->persist($courtOrder);

            $this->em->flush();
        }

        $this->em->persist($org);
        $this->em->flush();
    }

    /**
     * @Given I press the search button
     */
    public function iSearchUsingTheFilter(): void
    {
        $this->findAllCssElements('#search_submit')[0]->click();
    }
}
