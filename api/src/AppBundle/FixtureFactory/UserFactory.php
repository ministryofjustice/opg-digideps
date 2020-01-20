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
        $user = (new User())
            ->setFirstname(ucfirst($data['deputyType']) . ' Deputy ' . $data['id'])
            ->setLastname('User')
            ->setEmail(isset($data['email']) ? $data['email'] : 'behat-' . strtolower($data['deputyType']) .  '-deputy-' . $data['id'] . '@publicguardian.gov.uk')
            ->setActive(true)
            ->setRegistrationDate(new \DateTime())
            ->setNdrEnabled(isset($data['ndr']))
            ->setCoDeputyClientConfirmed(isset($data['codeputyEnabled']))
            ->setPhoneMain('07911111111111')
            ->setAddress1('Victoria Road')
            ->setAddressPostcode('SW1')
            ->setAddressCountry('GB')
            ->setRoleName($data['deputyType'] === 'LAY' ? 'ROLE_LAY_DEPUTY' : 'ROLE_' . $data['deputyType'] . '_NAMED');

        $user->setPassword($this->encoder->encodePassword($user, 'Abcd1234'));

        return $user;
    }
}
