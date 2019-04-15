<?php
namespace AppBundle\DataFixtures;

use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserPasswordFixtures extends AbstractDataFixture implements OrderedFixtureInterface
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function doLoad(ObjectManager $manager)
    {
        // Set all user passwords
        $userRepository = $manager->getRepository(User::class);
        $users = $userRepository->findAll('');

        $password = $this->container->getParameter('fixtures')['account_password'];

        foreach ($users as $user) {
            $user->setPassword($this->encoder->encodePassword($user, $password));
            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 11;
    }

    protected function getEnvironments()
    {
        return ['dev', 'test'];
    }
}
