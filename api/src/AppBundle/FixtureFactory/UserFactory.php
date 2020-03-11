<?php

namespace AppBundle\FixtureFactory;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFactory
{
    /** @var UserPasswordEncoderInterface  */
    private $encoder;

    /**
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function create(array $data): User
    {
        $roleName = $this->convertRoleName($data['deputyType']);

        $user = (new User())
            ->setFirstname(isset($data['firstName']) ? $data['firstName'] : ucfirst($data['deputyType']) . ' Deputy ' . $data['id'])
            ->setLastname(isset($data['lastName']) ? $data['lastName'] : 'User')
            ->setEmail(isset($data['email']) ? $data['email'] : 'behat-' . strtolower($data['deputyType']) .  '-deputy-' . $data['id'] . '@publicguardian.gov.uk')
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled(strtolower($data['ndr']) === 'enabled' ? true : false)
            ->setCoDeputyClientConfirmed(isset($data['codeputyEnabled']))
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressPostcode(isset($data['postCode']) ? $data['postCode'] : 'SW1')
            ->setAddressCountry('GB')
            ->setRoleName($roleName);

        if ($data['activated'] !== 'true') {
            $user->setPassword($this->encoder->encodePassword($user, 'Abcd1234'))
                ->setActive(false);
        }

        return $user;
    }

    /**
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function createAdmin(array $data): User
    {
        $user = (new User())
            ->setFirstname(isset($data['firstName']) ? $data['firstName'] : ucfirst($data['adminType']) . ' Admin ' . $data['email'])
            ->setLastname(isset($data['lastName']) ? $data['lastName'] : 'User')
            ->setEmail($data['email'])
            ->setRegistrationDate(new \DateTime())
            ->setRoleName($data['adminType']);

        if ($data['activated'] === 'true') {
            $user->setPassword($this->encoder->encodePassword($user, 'Abcd1234'))->setActive(true);
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
            default:
                return 'ROLE_' . $roleName . '_NAMED';
        }

    }
}
