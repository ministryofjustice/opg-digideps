<?php

namespace App\FixtureFactory;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\User;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFactory
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * @throws \Exception
     */
    public function create(array $data): User
    {
        $roleName = $this->convertRoleName($data['deputyType']);

        if (isset($data['ndr'])) {
            $ndrEnabled = 'enabled' === strtolower($data['ndr']) ? true : false;
        } else {
            $ndrEnabled = false;
        }

        $user = (new User())
            ->setFirstname(isset($data['firstName']) ? $data['firstName'] : ucfirst($data['deputyType']).' Deputy '.$data['id'])
            ->setLastname(isset($data['lastName']) ? $data['lastName'] : 'User')
            ->setEmail(isset($data['email']) ? $data['email'] : 'behat-'.strtolower($data['deputyType']).'-deputy-'.$data['id'].'@publicguardian.gov.uk')
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled($ndrEnabled)
            ->setCoDeputyClientConfirmed(isset($data['codeputyEnabled']))
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressPostcode(isset($data['postCode']) ? $data['postCode'] : 'SW1')
            ->setAddressCountry('GB')
            ->setRoleName($roleName)
            ->setAgreeTermsUse(true)
            ->setDeputyUid(User::TYPE_LAY === $data['deputyType'] ? $data['deputyUid'] : null);

        if ('true' === $data['activated'] || true === $data['activated']) {
            $user->setPassword($this->passwordHasher->hashPassword($user, 'DigidepsPass1234'));
        } else {
            $user->setActive(false);
        }

        return $user;
    }

    private function convertRoleName(string $roleName): string
    {
        switch ($roleName) {
            case 'LAY':
                return 'ROLE_LAY_DEPUTY';
            case 'AD':
                return 'ROLE_AD';
            case 'ADMIN':
                return 'ROLE_ADMIN';
            case 'PA_TEAM_MEMBER':
                return 'ROLE_PA_TEAM_MEMBER';
            case 'PA_ADMIN':
                return 'ROLE_PA_ADMIN';
            case 'PROF_TEAM_MEMBER':
                return 'ROLE_PROF_TEAM_MEMBER';
            case 'PROF_ADMIN':
                return 'ROLE_PROF_ADMIN';
            default:
                return 'ROLE_'.$roleName.'_NAMED';
        }
    }

    /**
     * @throws \Exception
     */
    public function createAdmin(array $data): User
    {
        $user = (new User())
            ->setFirstname(isset($data['firstName']) ? $data['firstName'] : ucfirst($data['adminType']).' Admin '.$data['email'])
            ->setLastname(isset($data['lastName']) ? $data['lastName'] : 'User')
            ->setEmail($data['email'])
            ->setRegistrationDate(new \DateTime())
            ->setRoleName($data['adminType']);

        if ('true' === $data['activated']) {
            $user->setPassword($this->passwordHasher->hashPassword($user, 'DigidepsPass1234'))->setActive(true);
        }

        return $user;
    }

    /**
     * @return User|void
     */
    public function createGenericOrgUser(Organisation $organisation)
    {
        $faker = Factory::create();

        $email = sprintf('%s.%s@%s', $faker->firstName(), $faker->lastName(), $organisation->getEmailIdentifier());
        $trimmedEmail = substr($email, 0, 59);

        $user = (new User())
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setEmail($trimmedEmail)
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled(false)
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressPostcode('SW1')
            ->setAddressCountry('GB')
            ->setRoleName('ROLE_PROF_TEAM_MEMBER');

        $user->setPassword($this->passwordHasher->hashPassword($user, 'DigidepsPass1234'));

        return $user;
    }

    public function createCoDeputy(User $originalDeputy, Client $client, array $data)
    {
        $user2 = clone $originalDeputy;
        $user2->setLastname($user2->getLastname().'-2')
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
            ->setDeputyUid('7'.str_pad((string) rand(1, 99999999), 11, '0', STR_PAD_LEFT));

        if ('true' === $data['activated'] || true === $data['activated']) {
            $user2->setPassword($this->passwordHasher->hashPassword($user2, 'DigidepsPass1234'))
                  ->setIsPrimary(true);
        } else {
            $user2->setActive(false);
        }

        return $user2;
    }
}
