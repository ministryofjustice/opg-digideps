<?php

namespace App\Tests\Unit\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Unit\Fixtures;
use DateTime;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserRepositoryTest extends WebTestCase
{
    /**
     * @var UserRepository
     */
    private $sut;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->em = $kernel->getContainer()->get('doctrine')->getManager();
        $this->fixtures = new Fixtures($this->em);

        $this->sut = $this->em->getRepository(User::class);

        $purger = new ORMPurger($this->em);
        $purger->purge();
    }

    public function testCountsInactiveUsers()
    {
        $oldUserWithNoClient = $this->fixtures->createUser();
        $oldUserWithNoClient->setRegistrationDate(DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldUserWithNoClient->setRoleName(User::ROLE_LAY_DEPUTY);

        $oldUserWithNoReports = $this->fixtures->createUser();
        $oldUserWithNoReports->setRegistrationDate(DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldUserWithNoReports->setRoleName(User::ROLE_LAY_DEPUTY);
        $this->fixtures->createClient($oldUserWithNoReports);

        $oldUserWithReport = $this->fixtures->createUser();
        $oldUserWithReport->setRegistrationDate(DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldUserWithReport->setRoleName(User::ROLE_LAY_DEPUTY);
        $oldClientWithReport = $this->fixtures->createClient($oldUserWithReport);
        $this->fixtures->createReport($oldClientWithReport);

        $oldUserWithNdr = $this->fixtures->createUser();
        $oldUserWithNdr->setRegistrationDate(DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldUserWithNdr->setRoleName(User::ROLE_LAY_DEPUTY);
        $oldClientWithNdr = $this->fixtures->createClient($oldUserWithNdr);
        $this->fixtures->createNdr($oldClientWithNdr);

        $oldProfUserWithNoClient = $this->fixtures->createUser();
        $oldProfUserWithNoClient->setRegistrationDate(DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldProfUserWithNoClient->setRoleName(User::ROLE_PROF_ADMIN);

        $recentUserWithNoClient = $this->fixtures->createUser();
        $recentUserWithNoClient->setRegistrationDate(new DateTime());
        $recentUserWithNoClient->setRoleName(User::ROLE_LAY_DEPUTY);
        $this->fixtures->createClient($recentUserWithNoClient);

        $this->em->flush();

        $inactiveUsers = $this->sut->findInactive();

        self::assertCount(2, $inactiveUsers);
    }

    /** @test */
    public function findActiveLaysInLastYear()
    {
        $userHelper = new UserTestHelper();
        $reportHelper = new ReportTestHelper();
        $clientHelper = new ClientTestHelper();

        $clientOne = $clientHelper->generateClient($this->em);
        $activeUserOne = ($userHelper->createAndPersistUser($this->em, $clientOne));
        $reportOne = ($reportHelper->generateReport($this->em, $clientOne))->setSubmitDate(new DateTime());

        $clientTwo = $clientHelper->generateClient($this->em);
        $activeUserTwo = $userHelper->createAndPersistUser($this->em, $clientTwo);
        $reportTwo = ($reportHelper->generateReport($this->em, $clientTwo))->setSubmitDate(new DateTime());

        $clientThree = $clientHelper->generateClient($this->em);
        $reportThree = ($reportHelper->generateReport($this->em, $clientThree))->setSubmitDate(new DateTime());
        $inactiveUserOne = $userHelper->createAndPersistUser($this->em, $clientThree);
        $inactiveUserOne->setLastLoggedIn(new DateTime('-380 days'));

        $clientFour = $clientHelper->generateClient($this->em);
        $reportFour = $reportHelper->generateReport($this->em, $clientFour);
        $inactiveUserTwo = $userHelper->createAndPersistUser($this->em, $clientFour);
        $inactiveUserTwo->setLastLoggedIn(new DateTime());

        $this->em->persist($inactiveUserOne);
        $this->em->persist($inactiveUserTwo);
        $this->em->persist($reportOne);
        $this->em->persist($reportTwo);
        $this->em->persist($reportThree);
        $this->em->persist($reportFour);
        $this->em->persist($clientOne);
        $this->em->persist($clientTwo);
        $this->em->persist($clientThree);
        $this->em->persist($clientFour);
        $this->em->flush();

        $results = $this->sut->findActiveLaysInLastYear();
        $resultsUserIds = [];

        foreach ($results as $userData) {
            $resultsUserIds[] = $userData['id'];
        }

        self::assertContains($activeUserOne->getId(), $resultsUserIds);
        self::assertContains($activeUserTwo->getId(), $resultsUserIds);
        self::assertNotContains($inactiveUserOne->getId(), $resultsUserIds);
        self::assertNotContains($inactiveUserTwo->getId(), $resultsUserIds);
    }

    /** @test */
    public function getAllAdminAccounts()
    {
        $userHelper = new UserTestHelper();
        $user = $userHelper->createUser(null, User::ROLE_ADMIN);

        $this->em->persist($user);
        $this->em->flush();
        $adminUsers = $this->sut->getAllAdminAccounts();
        self::assertEquals(1, count($adminUsers));
        self::assertContains($user, $adminUsers);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }
}
