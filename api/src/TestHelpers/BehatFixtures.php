<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

// Not extending AbstractDataFixture so we can use this in test runs rather commands
class BehatFixtures
{
    private EntityManagerInterface $entityManager;
    private array $fixtureParams;
    private UserPasswordEncoderInterface $encoder;

    public function __construct(
        EntityManagerInterface $entityManager,
        array $fixtureParams,
        UserPasswordEncoderInterface $encoder
    ) {
        $this->entityManager = $entityManager;
        $this->fixtureParams = $fixtureParams;
        $this->encoder = $encoder;
    }

    public function loadFixtures()
    {
        $purger = new ORMPurger($this->entityManager);
        $purger->purge();

        // Add admin users
        $adminUser = (new User())
            ->setFirstname('Admin')
            ->setLastname('User')
            ->setEmail('admin@publicguardian.gov.uk')
            ->setActive(true)
            ->setRoleName('ROLE_ADMIN');

        $adminUser->setPassword($this->encoder->encodePassword($adminUser, $this->fixtureParams['account_password']));

        $superAdminUser = (new User())
            ->setFirstname('Super Admin')
            ->setLastname('User')
            ->setEmail('super-admin@publicguardian.gov.uk')
            ->setActive(true)
            ->setRoleName('ROLE_SUPER_ADMIN');

        $superAdminUser->setPassword($this->encoder->encodePassword($superAdminUser, $this->fixtureParams['account_password']));

        $this->entityManager->persist($adminUser);
        $this->entityManager->persist($superAdminUser);

        $this->entityManager->flush();
    }
}
