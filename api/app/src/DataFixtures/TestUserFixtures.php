<?php

namespace OPG\Digideps\Backend\DataFixtures;

use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Fixture\FixtureService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TestUserFixtures extends AbstractDataFixture
{
    /** @var array[]  */
    private array $userData = [
        [
            'id' => 'deputy',
            'roleName' => 'ROLE_LAY_DEPUTY',
            'deputyUid' => 123321456654,
        ],
        [
            'id' => 'multi-client-primary-deputy',
            'roleName' => 'ROLE_LAY_DEPUTY',
            'deputyUid' => 567890098765,
            'isPrimary' => true,
        ],
        [
            'id' => 'multi-client-non-primary-deputy',
            'roleName' => 'ROLE_LAY_DEPUTY',
            'deputyUid' => 567890098765,
        ],
        [
            'id' => 'main-deputy',
            'roleName' => 'ROLE_LAY_DEPUTY',
            'deputyUid' => 987654321001,
            'co-deputy' => true,
        ],
        [
            'id' => 'co-deputy',
            'roleName' => 'ROLE_LAY_DEPUTY',
            'deputyUid' => 987654321002,
            'co-deputy' => true,
        ],
        [
            'id' => 'admin',
            'roleName' => 'ROLE_ADMIN',
        ],
        [
            'id' => 'super_admin',
            'roleName' => 'ROLE_SUPER_ADMIN',
        ],
        [
            'id' => 'pa',
            'roleName' => 'ROLE_PA_NAMED',
        ],
        [
            'id' => 'pa_admin',
            'roleName' => 'ROLE_PA_ADMIN',
        ],
        [
            'id' => 'pa_team_member',
            'roleName' => 'ROLE_PA_TEAM_MEMBER',
        ],
        [
            'id' => 'prof',
            'roleName' => 'ROLE_PROF_NAMED',
        ],
    ];

    public function __construct(
        public readonly KernelInterface $kernel,
        private readonly ParameterBagInterface $params,
        private readonly UserPasswordHasherInterface $passwordHasher,
        FixtureService $fixtureService
    ) {
        parent::__construct($kernel, $this->params, $fixtureService);
    }

    public function doLoad(FixtureService $fixtureService): void
    {
        // Add users from array
        foreach ($this->userData as $data) {
            $this->addUser($data, $fixtureService);
        }

        $fixtureService->flush();
    }

    private function addUser(array $data, FixtureService $fixtureService): void
    {
        $plain = ((array)$this->params->get('fixtures'))['account_password'] ?? null;
        if (!is_string($plain)) {
            throw new \DomainException("Fixtures password must be configured and be a string");
        }

        // Create user
        $user = new User(
            'test',
            $data['id'],
            $data['id'] . '@example.org'
        )
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressPostcode('SW1')
            ->setAddressCountry('GB')
            ->setRoleName($data['roleName'])
            ->setDeputyUid($data['deputyUid'] ?? null)
            ->setCoDeputyClientConfirmed($data['co-deputy'] ?? false)
            ->setIsPrimary($data['isPrimary'] ?? false);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plain));

        $fixtureService->persist($user);
    }

    protected function shouldLoad(string $workspace, string $environment): bool
    {
        return !in_array($workspace, ['production', 'preproduction']) && $environment === 'test';
    }
}
