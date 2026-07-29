<?php

namespace OPG\Digideps\Backend\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Fixture\FixtureService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordFixtures extends AbstractDataFixture implements OrderedFixtureInterface
{
    public function __construct(
        public readonly KernelInterface $kernel,
        private readonly ParameterBagInterface $params,
        private readonly UserPasswordHasherInterface $passwordHasher,
        FixtureService $fixtureService
    ) {
        parent::__construct($kernel, $fixtureService);
    }

    public function doLoad(ObjectManager $manager): void
    {
        // Set all user passwords
        $userRepository = $manager->getRepository(User::class);
        $users = $userRepository->findAll();

        $password = $this->params->get('fixtures')['account_password'];

        $passwordHash = null;
        foreach ($users as $user) {
            if (!$passwordHash) {
                // Re-use the same password hash for all users for efficiency purposes
                $passwordHash = $this->passwordHasher->hashPassword($user, $password);
            }
            $user->setPassword($passwordHash);
            $manager->persist($user);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 11;
    }

    /** @Return String[] */
    protected function getEnvironments(): array
    {
        return ['dev', 'test', 'local'];
    }
}
