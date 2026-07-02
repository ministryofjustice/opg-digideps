<?php

namespace OPG\Digideps\Backend\FixtureFactory;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactory
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher) {}

    /**
     * @throws \Exception
     */
    public function create(array $data): User
    {
        $roleName = $this->convertRoleName($data['deputyType']);

        $user = new User()
            ->setFirstname($data['firstName'] ?? ucfirst($data['deputyType']) . ' Deputy ' . $data['id'])
            ->setLastname($data['lastName'] ?? 'User')
            ->setEmail($data['email'] ?? 'behat-' . strtolower($data['deputyType']) . '-deputy-' . $data['id'] . '@publicguardian.gov.uk')
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setCoDeputyClientConfirmed(isset($data['codeputyEnabled']))
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressPostcode($data['postCode'] ?? 'SW1')
            ->setAddressCountry('GB')
            ->setRoleName($roleName)
            ->setAgreeTermsUse(true)
            ->setDeputyUid($data['deputyType'] === User::TYPE_LAY ? ($data['deputyUid'] ?? null) : null);

        if ($data['activated'] === 'true' || $data['activated'] === true) {
            $user->setPassword($this->passwordHasher->hashPassword($user, 'DigidepsPass1234'));
        } else {
            $user->setActive(false);
        }

        return $user;
    }

    private function convertRoleName(string $roleName): string
    {
        return match ($roleName) {
            'LAY' => 'ROLE_LAY_DEPUTY',
            'AD' => 'ROLE_AD',
            'ADMIN' => 'ROLE_ADMIN',
            'PA_TEAM_MEMBER' => 'ROLE_PA_TEAM_MEMBER',
            'PA_ADMIN' => 'ROLE_PA_ADMIN',
            'PROF_TEAM_MEMBER' => 'ROLE_PROF_TEAM_MEMBER',
            'PROF_ADMIN' => 'ROLE_PROF_ADMIN',
            default => 'ROLE_' . $roleName . '_NAMED',
        };
    }

    /**
     * @throws \Exception
     */
    public function createAdmin(array $data): User
    {
        $user = new User()
            ->setFirstname(isset($data['firstName']) ? $data['firstName'] : ucfirst($data['adminType']) . ' Admin ' . $data['email'])
            ->setLastname(isset($data['lastName']) ? $data['lastName'] : 'User')
            ->setEmail($data['email'])
            ->setRegistrationDate(new \DateTime())
            ->setRoleName($data['adminType']);

        if ($data['activated'] === 'true') {
            $user->setPassword($this->passwordHasher->hashPassword($user, 'DigidepsPass1234'))->setActive(true);
        }

        return $user;
    }

    public function createGenericOrgUser(Organisation $organisation, int $number): User
    {
        $email = sprintf('%s.%s.%s.%s@%s', 'Test', 'Org', rand(1, 100000), $number, $organisation->getEmailIdentifier());
        $trimmedEmail = substr($email, 0, 59);

        $user = new User()
            ->setFirstname('Bill')
            ->setLastname('Bonds')
            ->setEmail($trimmedEmail)
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressPostcode('SW1')
            ->setAddressCountry('GB')
            ->setRoleName('ROLE_PROF_TEAM_MEMBER');

        $user->setPassword($this->passwordHasher->hashPassword($user, 'DigidepsPass1234'));

        return $user;
    }

    public function createCoDeputy(User $originalDeputy, Client $client, array $data): User
    {
        $user2 = clone $originalDeputy;
        $user2->setLastname($user2->getLastname() . '-2')
            ->setEmail(
                sprintf(
                    'co-%s-deputy-%d@fixture.com',
                    strtolower($data['deputyType']),
                    mt_rand(1000, 99999)
                )
            )
            ->addClient($client)
            ->setActive($data['activated'])
            ->setRegistrationDate(new \DateTime())
            ->setCoDeputyClientConfirmed(true)
            ->setActive(true)
            ->setDeputyUid('7' . str_pad((string) rand(1, 99999999), 11, '0', STR_PAD_LEFT));

        if ($data['activated'] === 'true' || $data['activated'] === true) {
            $user2->setPassword($this->passwordHasher->hashPassword($user2, 'DigidepsPass1234'))
                  ->setIsPrimary(true);
        } else {
            $user2->setActive(false);
        }

        return $user2;
    }
}
