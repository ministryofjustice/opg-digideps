<?php

namespace OPG\Digideps\Backend\DataFixtures;

use OPG\Digideps\Backend\Fixture\FixtureService;
use OPG\Digideps\Backend\Fixture\UserType;
use OPG\Digideps\Common\Deputy\DeputyType;

/**
 * @phpstan-type FixtureData array{'firstName': string, 'lastName': string, 'email': string, 'roleType': UserType}
 */
class AdminUserFixtures extends AbstractDataFixture
{
    /**
     * @var array<FixtureData>
     */
    private array $adminData = [
        [
            'firstName' => 'Admin',
            'lastName' => 'User',
            'email' => 'admin@publicguardian.gov.uk',
            'roleType' => UserType::Admin,
        ],
        [
            'firstName' => 'Admin Manager',
            'lastName' => 'User',
            'email' => 'admin-manager@publicguardian.gov.uk',
            'roleType' => UserType::AdminManager,
        ],
        [
            'firstName' => 'Super Admin',
            'lastName' => 'User',
            'email' => 'super-admin@publicguardian.gov.uk',
            'roleType' => UserType::SuperAdmin,
        ],
        [
            'firstName' => 'Case',
            'lastName' => 'Manager1',
            'email' => 'casemanager1@publicguardian.gov.uk',
            'roleType' => UserType::Admin,
        ],
        [
            'firstName' => 'Case',
            'lastName' => 'Manager2',
            'email' => 'casemanager2@publicguardian.gov.uk',
            'roleType' => UserType::Admin,
        ],
        [
            'firstName' => 'Case',
            'lastName' => 'Manager3',
            'email' => 'casemanager3@publicguardian.gov.uk',
            'roleType' => UserType::Admin,
        ],
    ];

    public function doLoad(FixtureService $fixtureService): void
    {
        // Add admin users
        foreach ($this->adminData as $data) {
            $this->addUser($fixtureService, $data);
        }

        $fixtureService->flush();
    }

    /**
     * @param FixtureData $data
     */
    private function addUser(FixtureService $fixtureService, array $data): void
    {
        $fixtureService->instantiateOnlyUser($data['roleType'], DeputyType::LAY, flush: false)
            ->setFirstname($data['firstName'])
            ->setLastname($data['lastName'])
            ->setEmail($data['email']);
    }

    protected function shouldLoad(string $workspace, string $environment): bool
    {
        return !in_array($workspace, ['production', 'preproduction']) && in_array($environment, ['dev', 'local']);
    }
}
