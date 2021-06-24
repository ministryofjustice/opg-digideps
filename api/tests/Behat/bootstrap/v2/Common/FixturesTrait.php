<?php

declare(strict_types=1);

namespace App\Tests\Behat\v2\Common;

use App\Entity\User;
use App\Tests\Behat\v2\Helpers\FixtureHelper;
use Behat\Gherkin\Node\TableNode;

trait FixturesTrait
{
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

    /**
     * @param $row
     */
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
     * @Given two clients have the same first name
     * @Given two clients have the same last name
     */
    public function twoClientsHaveSameNames()
    {
        $this->fixtureHelper->duplicateClient($this->layDeputyNotStartedPfaHighAssetsDetails->getClientId());
        $this->interactingWithUserDetails = $this->layDeputyNotStartedPfaHighAssetsDetails;
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

    private function createAdditionalAdminUser(string $roleName)
    {
        $email = sprintf('%s@t.uk', rand(0, 999999999));

        return $this->fixtureHelper->createAndPersistUser($roleName, $email);
    }

    public function assertInteractingWithUserIsSet()
    {
        if (is_null($this->interactingWithUserDetails)) {
            $this->throwContextualException(
                'An interacting with User has not been set. Ensure a previous step in the scenario has set this User and try again.'
            );
        }
    }

    public function createAdditionalProfAdminUsers(int $numberOfUsers)
    {
        $users = [];

        for ($i = 0; $i < $numberOfUsers; ++$i) {
            $userDetails = $this->fixtureHelper->createProfAdminNotStarted($this->testRunId.'-f'.$i);
            $users[] = new UserDetails($userDetails);
        }

        return $users;
    }
}
