<?php

namespace App\Tests\Unit\Entity\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Unit\Fixtures;
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
        $oldUserWithNoClient->setRegistrationDate(\DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldUserWithNoClient->setRoleName(User::ROLE_LAY_DEPUTY);

        $oldUserWithNoReports = $this->fixtures->createUser();
        $oldUserWithNoReports->setRegistrationDate(\DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldUserWithNoReports->setRoleName(User::ROLE_LAY_DEPUTY);
        $this->fixtures->createClient($oldUserWithNoReports);

        $oldUserWithReport = $this->fixtures->createUser();
        $oldUserWithReport->setRegistrationDate(\DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldUserWithReport->setRoleName(User::ROLE_LAY_DEPUTY);
        $oldClientWithReport = $this->fixtures->createClient($oldUserWithReport);
        $this->fixtures->createReport($oldClientWithReport);

        $oldUserWithNdr = $this->fixtures->createUser();
        $oldUserWithNdr->setRegistrationDate(\DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldUserWithNdr->setRoleName(User::ROLE_LAY_DEPUTY);
        $oldClientWithNdr = $this->fixtures->createClient($oldUserWithNdr);
        $this->fixtures->createNdr($oldClientWithNdr);

        $oldProfUserWithNoClient = $this->fixtures->createUser();
        $oldProfUserWithNoClient->setRegistrationDate(\DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldProfUserWithNoClient->setRoleName(User::ROLE_PROF_ADMIN);

        $recentUserWithNoClient = $this->fixtures->createUser();
        $recentUserWithNoClient->setRegistrationDate(new \DateTime());
        $recentUserWithNoClient->setRoleName(User::ROLE_LAY_DEPUTY);
        $this->fixtures->createClient($recentUserWithNoClient);

        $this->em->flush();

        $inactiveUsers = $this->sut->findInactive();

        self::assertCount(2, $inactiveUsers);
    }

    public function testFindActiveLaysInLastYear()
    {
        $userHelper = new UserTestHelper();
        $reportHelper = new ReportTestHelper();
        $clientHelper = new ClientTestHelper();

        $clientOne = $clientHelper->generateClient($this->em);
        $activeUserOne = $userHelper->createAndPersistUser($this->em, $clientOne);
        $reportOne = $reportHelper->generateReport($this->em, $clientOne)->setSubmitDate(new \DateTime());

        $clientTwo = $clientHelper->generateClient($this->em);
        $activeUserTwo = $userHelper->createAndPersistUser($this->em, $clientTwo);
        $reportTwo = $reportHelper->generateReport($this->em, $clientTwo)->setSubmitDate(new \DateTime());

        $clientThree = $clientHelper->generateClient($this->em);
        $reportThree = $reportHelper->generateReport($this->em, $clientThree)->setSubmitDate(new \DateTime());
        $inactiveUserOne = $userHelper->createAndPersistUser($this->em, $clientThree);
        $inactiveUserOne->setLastLoggedIn(new \DateTime('-380 days'));

        $clientFour = $clientHelper->generateClient($this->em);
        $reportFour = $reportHelper->generateReport($this->em, $clientFour);
        $inactiveUserTwo = $userHelper->createAndPersistUser($this->em, $clientFour);
        $inactiveUserTwo->setLastLoggedIn(new \DateTime());

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

    public function testGetAllAdminAccounts()
    {
        $userHelper = new UserTestHelper();
        $usersToAdd = [];
        $usersToAdd[] = $adminUser = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $adminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $superAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $layDeputyUser = $userHelper->createUser(null, User::ROLE_LAY_DEPUTY);
        $usersToAdd[] = $profDeputyUser = $userHelper->createUser(null, User::ROLE_PROF_ADMIN);
        $usersToAdd[] = $paDeputyUser = $userHelper->createUser(null, User::ROLE_PROF_ADMIN);

        foreach ($usersToAdd as $user) {
            $this->em->persist($user);
        }

        $this->em->flush();

        $expectedAdminUsersReturned = [$adminUser, $adminManagerUser, $superAdminUser];
        $expectedDeputyUsersNotReturned = [$layDeputyUser, $profDeputyUser, $paDeputyUser];
        $actualAdminUsers = $this->sut->getAllAdminAccounts();

        self::assertEquals($expectedAdminUsersReturned, $actualAdminUsers);

        foreach ($expectedDeputyUsersNotReturned as $deputyUser) {
            self::assertNotContains($deputyUser, $actualAdminUsers);
        }
    }

    public function testGetAllAdminAccountsCreatedButNotActivatedWithin()
    {
        $userHelper = new UserTestHelper();
        $usersToAdd = [];
        $usersToAdd[] = $adminUserMoreThan60Days = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $superAdminUserMoreThan60Days = $userHelper->createUser(null, User::ROLE_SUPER_ADMIN);
        $usersToAdd[] = $adminManagerUserMoreThan60Days = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $adminUserLessThan60Days = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $nonAdminUserLessThan60Days = $userHelper->createUser(null, User::ROLE_LAY_DEPUTY);

        $adminUserMoreThan60Days->setRegistrationDate(new \DateTime('-61 days'));
        $adminUserMoreThan60Days->setLastLoggedIn(null);
        $superAdminUserMoreThan60Days->setRegistrationDate(new \DateTime('-61 days'));
        $superAdminUserMoreThan60Days->setLastLoggedIn(null);
        $adminManagerUserMoreThan60Days->setRegistrationDate(new \DateTime('-61 days'));
        $adminManagerUserMoreThan60Days->setLastLoggedIn(null);
        $adminUserLessThan60Days->setRegistrationDate(new \DateTime('-5 days'));
        $adminUserLessThan60Days->setLastLoggedIn(new \DateTime());
        $nonAdminUserLessThan60Days->setRegistrationDate(new \DateTime('-61 days'));
        $nonAdminUserLessThan60Days->setLastLoggedIn(null);

        foreach ($usersToAdd as $user) {
            $this->em->persist($user);
        }

        $this->em->flush();

        $expectedAdminUsersReturned = [$adminUserMoreThan60Days, $superAdminUserMoreThan60Days, $adminManagerUserMoreThan60Days];
        $expectedAdminUsersNotReturned = [$adminUserLessThan60Days, $nonAdminUserLessThan60Days];

        $actualAdminUsers = $this->sut->getAllAdminAccountsCreatedButNotActivatedWithin('-60 days');

        self::assertEquals($expectedAdminUsersReturned, $actualAdminUsers);

        foreach ($expectedAdminUsersNotReturned as $user) {
            self::assertNotContains($user, $actualAdminUsers);
        }
    }

    public function testGetAllActivatedAdminAccounts()
    {
        $userHelper = new UserTestHelper();
        $usersToAdd = [];
        $usersToAdd[] = $activeAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $activeSuperAdminUser = $userHelper->createUser(null, User::ROLE_SUPER_ADMIN);
        $usersToAdd[] = $activeAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $inactiveAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $activeDeputyUser = $userHelper->createUser(null, User::ROLE_LAY_DEPUTY);

        $activeAdminUser->setLastLoggedIn(new \DateTime());
        $activeSuperAdminUser->setLastLoggedIn(new \DateTime());
        $activeAdminManagerUser->setLastLoggedIn(new \DateTime());
        $inactiveAdminManagerUser->setLastLoggedIn();
        $activeDeputyUser->setLastLoggedIn(new \DateTime());

        foreach ($usersToAdd as $user) {
            $this->em->persist($user);
        }

        $this->em->flush();

        $expectedActiveAdminUsersReturned = [$activeAdminUser, $activeSuperAdminUser, $activeAdminManagerUser];
        $expectedAdminUsersNotReturned = [$inactiveAdminManagerUser, $activeDeputyUser];

        $actualAdminUsers = $this->sut->getAllActivatedAdminAccounts();

        self::assertEquals($expectedActiveAdminUsersReturned, $actualAdminUsers);

        foreach ($expectedAdminUsersNotReturned as $user) {
            self::assertNotContains($user, $actualAdminUsers);
        }
    }

    public function testGetAllAdminAccountsNotUsedWithin()
    {
        $userHelper = new UserTestHelper();
        $usersToAdd = [];
        $usersToAdd[] = $loggedInAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $loggedInSuperAdminUser = $userHelper->createUser(null, User::ROLE_SUPER_ADMIN);
        $usersToAdd[] = $loggedInAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $recentlyLoggedInAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $recentlyLoggedInDeputyUser = $userHelper->createUser(null, User::ROLE_LAY_DEPUTY);

        $loggedInAdminUser->setLastLoggedIn(new \DateTime('-95 days'));
        $loggedInSuperAdminUser->setLastLoggedIn(new \DateTime('-91 days'));
        $loggedInAdminManagerUser->setLastLoggedIn(new \DateTime('-91 days'));
        $recentlyLoggedInAdminManagerUser->setLastLoggedIn(new \DateTime('-1 day'));
        $recentlyLoggedInDeputyUser->setLastLoggedIn(new \DateTime('-1 day'));

        foreach ($usersToAdd as $user) {
            $this->em->persist($user);
        }

        $this->em->flush();

        $expectedLoggedInAdminUsers = [$loggedInAdminUser, $loggedInSuperAdminUser, $loggedInAdminManagerUser];
        $expectedRecentlyLoggedInUsersNotReturned = [$recentlyLoggedInAdminManagerUser, $recentlyLoggedInDeputyUser];

        $actualLoggedOutUsers = $this->sut->getAllAdminAccountsNotUsedWithin('-90 days');

        self::assertEquals($expectedLoggedInAdminUsers, $actualLoggedOutUsers);

        foreach ($expectedRecentlyLoggedInUsersNotReturned as $user) {
            self::assertNotContains($user, $actualLoggedOutUsers);
        }
    }

    public function testGetAllAdminAccountsUsedWithin()
    {
        $userHelper = new UserTestHelper();
        $usersToAdd = [];
        $usersToAdd[] = $loggedInAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $loggedInSuperAdminUser = $userHelper->createUser(null, User::ROLE_SUPER_ADMIN);
        $usersToAdd[] = $loggedInAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $notRecentlyLoggedInAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $notRecentlyLoggedInDeputyUser = $userHelper->createUser(null, User::ROLE_LAY_DEPUTY);

        $loggedInAdminUser->setLastLoggedIn(new \DateTime('-50 days'));
        $loggedInSuperAdminUser->setLastLoggedIn(new \DateTime('-50 days'));
        $loggedInAdminManagerUser->setLastLoggedIn(new \DateTime('-50 days'));
        $notRecentlyLoggedInAdminManagerUser->setLastLoggedIn(new \DateTime('-100 days'));
        $notRecentlyLoggedInDeputyUser->setLastLoggedIn(new \DateTime('-100 days'));

        foreach ($usersToAdd as $user) {
            $this->em->persist($user);
        }

        $this->em->flush();

        $expectedLoggedInAdminUsers = [$loggedInAdminUser, $loggedInSuperAdminUser, $loggedInAdminManagerUser];
        $expectedLoggedOutUsersNotReturned = [$notRecentlyLoggedInAdminManagerUser, $notRecentlyLoggedInDeputyUser];

        $actualLoggedInUsers = $this->sut->getAllAdminAccountsUsedWithin('-90 days');

        self::assertEquals($expectedLoggedInAdminUsers, $actualLoggedInUsers);

        foreach ($expectedLoggedOutUsersNotReturned as $user) {
            self::assertNotContains($user, $actualLoggedInUsers);
        }
    }

    public function testGetAllAdminUserAccountsNotUsedWithin()
    {
        $userHelper = new UserTestHelper();
        $usersToAdd = [];
        $usersToAdd[] = $notRecentlyLoggedInAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $notRecentlyLoggedInSuperAdminManagerUser = $userHelper->createUser(null, User::ROLE_SUPER_ADMIN);
        $usersToAdd[] = $recentlyLoggedInAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $recentlyLoggedInAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $recentlyLoggedInSuperAdminManagerUser = $userHelper->createUser(null, User::ROLE_SUPER_ADMIN);
        $usersToAdd[] = $recentlyLoggedInDeputyUser = $userHelper->createUser(null, User::ROLE_LAY_DEPUTY);

        $notRecentlyLoggedInAdminUser->setLastLoggedIn(new \DateTime('-13 months'));
        $notRecentlyLoggedInSuperAdminManagerUser->setLastLoggedIn(new \DateTime('-24 months'));
        $recentlyLoggedInAdminUser->setLastLoggedIn(new \DateTime('-10 days'));
        $recentlyLoggedInAdminManagerUser->setLastLoggedIn(new \DateTime('-10 days'));
        $recentlyLoggedInSuperAdminManagerUser->setLastLoggedIn(new \DateTime('-10 days'));

        $recentlyLoggedInDeputyUser->setLastLoggedIn(new \DateTime('-10 days'));

        foreach ($usersToAdd as $user) {
            $this->em->persist($user);
        }

        $this->em->flush();

        $expectedLoggedInAdminUsers = [$notRecentlyLoggedInAdminUser, $notRecentlyLoggedInSuperAdminManagerUser];
        $expectedRecentlyLoggedInUsersNotReturned = [$recentlyLoggedInAdminUser, $recentlyLoggedInAdminManagerUser, $recentlyLoggedInDeputyUser];

        $actualLoggedInAdminUsers = $this->sut->getAllAdminAccountsNotUsedWithin('-12 months');

        self::assertEquals($expectedLoggedInAdminUsers, $actualLoggedInAdminUsers);

        foreach ($expectedRecentlyLoggedInUsersNotReturned as $user) {
            self::assertNotContains($user, $actualLoggedInAdminUsers);
        }
    }

    public function testInactiveAdminUsersAreDeleted()
    {
        $userHelper = new UserTestHelper();

        $usersToAdd = [];
        $usersToAdd[] = $activeAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN)
            ->setLastLoggedIn(new \DateTime('-2 months'));
        $usersToAdd[] = $activeLayDeputyUser = $userHelper->createUser(null, User::ROLE_LAY_DEPUTY)
            ->setLastLoggedIn(new \DateTime('-2 months'));
        $usersToAdd[] = $inactiveAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN)
            ->setLastLoggedIn(new \DateTime('-25 months'));
        $usersToAdd[] = $inactiveAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER)
            ->setLastLoggedIn(new \DateTime('-26 months'));

        foreach ($usersToAdd as $user) {
            $this->em->persist($user);
        }

        $this->em->flush();

        $adminUserIds = [];
        foreach ($usersToAdd as $user) {
            $adminUserIds[] = $user->getId();
        }

        $this->sut->deleteInactiveAdminUsers($adminUserIds);

        $adminUsersDeleted = [$inactiveAdminUser->getId(), $inactiveAdminManagerUser->getId()];
        $usersNotDeleted = [$activeAdminUser->getId(), $activeLayDeputyUser->getId()];

        $deletedAdminUsers = $this->sut->findBy(['id' => $adminUsersDeleted]);
        $this->assertCount(0, $deletedAdminUsers);

        $usersNotDeleted = $this->sut->findBy(['id' => $usersNotDeleted]);
        $this->assertCount(2, $usersNotDeleted);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }
}
