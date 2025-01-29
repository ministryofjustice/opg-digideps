<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\User;
use App\TestHelpers\UserTestHelper;
use App\Tests\Behat\BehatException;
use App\Tests\Behat\v2\Helpers\FixtureHelper;
use Behat\Gherkin\Node\TableNode;

trait FixturesTrait
{
    public array $sameFirstNameUserDetails = [];
    public array $sameLastNameUserDetails = [];
    public ?UserDetails $twoReportsUserDetails = null;
    public ?UserDetails $oneReportsUserDetails = null;

    public ?UserDetails $interactingWithUserDetails = null;
    private UserTestHelper $userTestHelper;

    /**
     * @Given the following court orders exist:
     */
    public function theFollowingCourtOrdersExist(TableNode $table)
    {
        $this->loginToAdminAs($this->superAdminDetails->getUserEmail());

        foreach ($table as $row) {
            $queryString = http_build_query([
                'case-number' => $row['client'],
                'court-date' => $row['court_date'],
                'deputy-email' => $row['deputy'].'@behat-test.com',
            ]);

            $url = sprintf('/admin/fixtures/court-orders?%s', $queryString);
            $this->visitAdminPath($url);

            $activated = is_null($row['activated']) || 'true' == $row['activated'];
            $this->fillField('court_order_fixture_activated', $activated);
            $this->fillField('court_order_fixture_deputyType', $row['deputy_type']);
            $this->fillField('court_order_fixture_reportType', $this->resolveReportType($row));
            $this->fillField('court_order_fixture_reportStatus', $row['completed'] ? 'readyToSubmit' : 'notStarted');
            $this->fillField('court_order_fixture_orgSizeClients', $row['orgSizeClients'] ? $row['orgSizeClients'] : 1);
            $this->fillField('court_order_fixture_orgSizeUsers', $row['orgSizeUsers'] ? $row['orgSizeUsers'] : 1);

            $this->pressButton('court_order_fixture_submit');
        }
    }

    private function resolveReportType($row): string
    {
        $typeFromFeatureFile = strtolower($row['report_type']);

        switch ($typeFromFeatureFile) {
            case 'health and welfare':
                return '104';
            case 'property and financial affairs high assets':
                return '102';
            case 'property and financial affairs low assets':
                return '103';
            case 'high assets with health and welfare':
                return '102-4';
            case 'low assets with health and welfare':
                return '103-4';
            case 'ndr':
                return 'ndr';
            default:
                return '102';
        }
    }

    /**
     * @Given two clients have the same :whichName name
     */
    public function twoClientsHaveSameNames(string $whichName)
    {
        $sameFirstname = in_array(strtolower($whichName), ['first', 'full']);
        $sameLastname = in_array(strtolower($whichName), ['last', 'full']);

        $this->fixtureHelper->duplicateClient($this->layDeputyNotStartedPfaHighAssetsDetails->getClientId(), $sameFirstname, $sameLastname);
        $this->interactingWithUserDetails = $this->layDeputyNotStartedPfaHighAssetsDetails;
    }

    /**
     * @Given two submitted reports with clients sharing the same :whichName name exist
     */
    public function twoClientsExistWithTheSameFirstName(string $whichName)
    {
        $userDetails1 = $this->createLayCombinedHighSubmitted(null, $this->testRunId.mt_rand(1, 10000));
        $client1 = $this->em->getRepository(Client::class)->find($userDetails1->getClientId());

        $firstName = $client1->getFirstname().time();
        $lastName = $client1->getLastname().time();

        $client1->setFirstname($firstName);
        $client1->setLastname($lastName);

        $userDetails2 = $this->createLayCombinedHighSubmitted(null, $this->testRunId.mt_rand(1, 10000));
        $client2 = $this->em->getRepository(Client::class)->find($userDetails2->getClientId());

        if ('first' === $whichName) {
            $userDetails1->setClientFirstName($firstName);
            $userDetails2->setClientFirstName($firstName);
            $client2->setFirstname($firstName);
            array_push($this->sameFirstNameUserDetails, $userDetails1, $userDetails2);
        } else {
            $userDetails1->setClientLastName($lastName);
            $userDetails2->setClientLastName($lastName);
            $client2->setLastname($lastName);
            array_push($this->sameLastNameUserDetails, $userDetails1, $userDetails2);
        }

        $this->em->persist($client1);
        $this->em->persist($client2);
        $this->em->flush();
    }

    /**
     * @Given a client has submitted two reports
     */
    public function aClientHasSubmittedTwoReports()
    {
        $userDetails = $this->twoReportsUserDetails = $this->createLayCombinedHighSubmitted(null, $this->testRunId.'A');
        $newReport = $this->em->getRepository(Report::class)->find($userDetails->getCurrentReportId());
        $this->reportTestHelper->submitReport($newReport, $this->em);
    }

    /**
     * @Given another/a client has submitted one report
     */
    public function anotherClientHasSubmittedOneReport()
    {
        $this->oneReportsUserDetails = $this->createLayCombinedHighSubmitted(null, $this->testRunId.'B');
    }

    /**
     * @Given another super admin user exists
     */
    public function anotherSuperAdminUserExists()
    {
        $user = $this->createAdditionalAdminUser(User::ROLE_SUPER_ADMIN);
        $this->interactingWithUserDetails = new UserDetails(FixtureHelper::buildAdminUserDetails($user));
    }

    /**
     * @Given another admin manager user exists
     */
    public function anotherAdminManagerUserExists()
    {
        $user = $this->createAdditionalAdminUser(User::ROLE_ADMIN_MANAGER);
        $this->interactingWithUserDetails = new UserDetails(FixtureHelper::buildAdminUserDetails($user));
    }

    /**
     * @Given another admin user exists
     */
    public function anotherAdminUserExists()
    {
        $user = $this->createAdditionalAdminUser(User::ROLE_ADMIN);
        $this->interactingWithUserDetails = new UserDetails(FixtureHelper::buildAdminUserDetails($user));
    }

    /**
     * @Given the Lay deputy user with deputy UID :deputyUid and email :email exists
     */
    public function theLayDeputyUserWithEmailExists($deputyUid, $email)
    {
        return $this->fixtureHelper->createAndPersistUser(User::ROLE_LAY_DEPUTY, $email, intval($deputyUid));
    }

    private function createAdditionalAdminUser(string $roleName)
    {
        $email = sprintf('%s@t.uk', rand(0, 999999999));

        return $this->fixtureHelper->createAndPersistUser($roleName, $email);
    }

    public function assertInteractingWithUserIsSet()
    {
        if (is_null($this->interactingWithUserDetails)) {
            throw new BehatException('An interacting with User has not been set. Ensure a previous step in the scenario has set this User and try again.');
        }
    }

    public function assertLoggedInUserIsSet()
    {
        if (is_null($this->loggedInUserDetails)) {
            throw new BehatException('An logged in User has not been set. Ensure a previous step in the scenario has set this User and try again.');
        }
    }

    public function assertLoggedInUserHasOrgRole()
    {
        if (!in_array($this->loggedInUserDetails->getUserRole(), User::$orgRoles)) {
            $message = sprintf('The logged in user role is "%s"/ Expected one of "%s".', $this->loggedInUserDetails->getUserRole(), implode(', ', User::$orgRoles));

            throw new BehatException($message);
        }
    }

    public function createAdditionalProfHealthWelfareUsers(int $numberOfUsers): array
    {
        $users = [];

        for ($i = 0; $i < $numberOfUsers; ++$i) {
            $userDetails = $this->fixtureHelper->createProfNamedHealthWelfareNotStarted($this->testRunId.'-f'.$i);
            $users[] = new UserDetails($userDetails);
        }

        return $users;
    }

    public function changeCaseNumber(int $clientId, string $newCaseNumber)
    {
        $this->fixtureHelper->changeCaseNumber($clientId, $newCaseNumber);
    }

    /**
     * @Given /^the user has \'([^\']*)\' permissions and another user exists within the same organisation$/
     */
    public function theUserHasPermissionsAndAnotherUserExistsWithinTheSameOrganisation(string $adminPermissions)
    {
        $setExistingUserFixture = $this->setExistingUser($adminPermissions);

        $existingUser = $this->interactingWithUserDetails = $setExistingUserFixture;
        $emailIdentifier = $existingUser->getOrganisationEmailIdentifier();

        $newUserEmail = sprintf('%s-%s@t.uk', substr(User::ROLE_PROF_TEAM_MEMBER, 5), $this->testRunId);

        $newUser = $this->fixtureHelper->createAndPersistUser(User::ROLE_PROF_TEAM_MEMBER, $newUserEmail);

        $organisation = $this->em->getRepository(Organisation::class)->findByEmailIdentifier($emailIdentifier);

        $organisation->addUser($newUser);

        $this->em->persist($organisation);
        $this->em->flush();
    }

    private function setExistingUser(string $adminPermissions)
    {
        if ('admin' === $adminPermissions) {
            return $this->profAdminCombinedHighNotStartedDetails;
        } else {
            return $this->profTeamDeputyNotStartedHealthWelfareDetails;
        }
    }
}
