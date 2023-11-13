<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordFixtures extends AbstractDataFixture implements OrderedFixtureInterface
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function doLoad(ObjectManager $manager)
    {
        // Set all user passwords
        $userRepository = $manager->getRepository(User::class);
        $users = $userRepository->findAll('');

        $password = $this->container->getParameter('fixtures')['account_password'];

        foreach ($users as $user) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
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
