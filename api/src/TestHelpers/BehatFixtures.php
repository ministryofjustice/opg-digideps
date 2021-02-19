<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

// Not extending AbstractDataFixture so we can use this in test runs rather commands
class BehatFixtures
{
    private EntityManagerInterface $entityManager;
    private array $fixtureParams;
    private UserPasswordEncoderInterface $encoder;
    private string $symfonyEnvironment;

    public function __construct(
        EntityManagerInterface $entityManager,
        array $fixtureParams,
        UserPasswordEncoderInterface $encoder,
        string $symfonyEnvironment
    ) {
        $this->entityManager = $entityManager;
        $this->fixtureParams = $fixtureParams;
        $this->encoder = $encoder;
        $this->symfonyEnvironment = $symfonyEnvironment;
    }

    /**
     * @param string $testRunId
     * @return array
     * @throws Exception
     */
    public function loadFixtures(string $testRunId)
    {
        if ($this->symfonyEnvironment === 'prod') {
            throw new Exception('Prod mode enabled - cannot purge database');
        }

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();

        // Add admin users
        $adminUser = (new User())
            ->setFirstname('Admin')
            ->setLastname('User')
            ->setEmail(sprintf('admin-%s@publicguardian.gov.uk', $testRunId))
            ->setActive(true)
            ->setRoleName('ROLE_ADMIN');

        $adminUser->setPassword($this->encoder->encodePassword($adminUser, $this->fixtureParams['account_password']));

        $superAdminUser = (new User())
            ->setFirstname('Super Admin')
            ->setLastname('User')
            ->setEmail(sprintf('super-admin-%s@publicguardian.gov.uk', $testRunId))
            ->setActive(true)
            ->setRoleName('ROLE_SUPER_ADMIN');

        $superAdminUser->setPassword($this->encoder->encodePassword($superAdminUser, $this->fixtureParams['account_password']));

        $this->entityManager->persist($adminUser);
        $this->entityManager->persist($superAdminUser);

        $this->entityManager->flush();

        return ['admin' => $adminUser->getEmail(), 'super-admin' => $superAdminUser->getEmail()];
    }
}
