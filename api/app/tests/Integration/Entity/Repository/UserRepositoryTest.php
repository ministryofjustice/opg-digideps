<?php

namespace App\Tests\Integration\Entity\Repository;

use App\Tests\Integration\ApiTestTrait;
use DateTime;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\Repository\UserRepository;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\DeputyTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Integration\Fixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    use ApiTestTrait;

    private static Fixtures $fixtures;
    private static UserRepository $sut;

    public function setUp(): void
    {
        parent::setUp();

        self::configureTest();

        self::$fixtures = new Fixtures(self::$entityManager);

        /** @var UserRepository $sut */
        $sut = self::$entityManager->getRepository(User::class);

        self::$sut = $sut;

        // clear all tables, including dd_user
        self::purgeDatabase([]);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        // clear all tables, including dd_user
        self::purgeDatabase([]);
    }

    public function testCountsInactiveUsers()
    {
        $oldUserWithNoClient = self::$fixtures->createUser();
        $oldUserWithNoClient->setRegistrationDate(DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldUserWithNoClient->setRoleName(User::ROLE_LAY_DEPUTY);

        $oldUserWithNoReports = self::$fixtures->createUser();
        $oldUserWithNoReports->setRegistrationDate(DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldUserWithNoReports->setRoleName(User::ROLE_LAY_DEPUTY);
        self::$fixtures->createClient($oldUserWithNoReports);

        $oldUserWithReport = self::$fixtures->createUser();
        $oldUserWithReport->setRegistrationDate(DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldUserWithReport->setRoleName(User::ROLE_LAY_DEPUTY);
        $oldClientWithReport = self::$fixtures->createClient($oldUserWithReport);
        self::$fixtures->createReport($oldClientWithReport);

        $oldUserWithNdr = self::$fixtures->createUser();
        $oldUserWithNdr->setRegistrationDate(DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldUserWithNdr->setRoleName(User::ROLE_LAY_DEPUTY);
        $oldClientWithNdr = self::$fixtures->createClient($oldUserWithNdr);
        self::$fixtures->createNdr($oldClientWithNdr);

        $oldProfUserWithNoClient = self::$fixtures->createUser();
        $oldProfUserWithNoClient->setRegistrationDate(DateTime::createFromFormat('Y-m-d', '2019-03-03'));
        $oldProfUserWithNoClient->setRoleName(User::ROLE_PROF_ADMIN);

        $recentUserWithNoClient = self::$fixtures->createUser();
        $recentUserWithNoClient->setRegistrationDate(new DateTime());
        $recentUserWithNoClient->setRoleName(User::ROLE_LAY_DEPUTY);
        self::$fixtures->createClient($recentUserWithNoClient);

        self::$entityManager->flush();

        $inactiveUsers = self::$sut->findInactive();

        self::assertCount(2, $inactiveUsers);
    }

    public function testFindActiveLaysInLastYear()
    {
        $userHelper = UserTestHelper::create();
        $reportHelper = ReportTestHelper::create();
        $clientHelper = ClientTestHelper::create();

        $clientOne = $clientHelper->generateClient(self::$entityManager);
        $activeUserOne = $userHelper->createAndPersistUser(self::$entityManager, $clientOne);
        $reportOne = $reportHelper->generateReport(self::$entityManager, $clientOne)->setSubmitDate(new DateTime());

        $clientTwo = $clientHelper->generateClient(self::$entityManager);
        $activeUserTwo = $userHelper->createAndPersistUser(self::$entityManager, $clientTwo);
        $reportTwo = $reportHelper->generateReport(self::$entityManager, $clientTwo)->setSubmitDate(new DateTime());

        $clientThree = $clientHelper->generateClient(self::$entityManager);
        $reportThree = $reportHelper->generateReport(self::$entityManager, $clientThree)->setSubmitDate(new DateTime());
        $inactiveUserOne = $userHelper->createAndPersistUser(self::$entityManager, $clientThree);
        $inactiveUserOne->setLastLoggedIn(new DateTime('-380 days'));

        $clientFour = $clientHelper->generateClient(self::$entityManager);
        $reportFour = $reportHelper->generateReport(self::$entityManager, $clientFour);
        $inactiveUserTwo = $userHelper->createAndPersistUser(self::$entityManager, $clientFour);
        $inactiveUserTwo->setLastLoggedIn(new DateTime());

        self::$entityManager->persist($inactiveUserOne);
        self::$entityManager->persist($inactiveUserTwo);
        self::$entityManager->persist($reportOne);
        self::$entityManager->persist($reportTwo);
        self::$entityManager->persist($reportThree);
        self::$entityManager->persist($reportFour);
        self::$entityManager->persist($clientOne);
        self::$entityManager->persist($clientTwo);
        self::$entityManager->persist($clientThree);
        self::$entityManager->persist($clientFour);
        self::$entityManager->flush();

        $results = self::$sut->findActiveLaysInLastYear();
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
        $userHelper = UserTestHelper::create();
        $usersToAdd = [];
        $usersToAdd[] = $adminUser = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $adminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $superAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $layDeputyUser = $userHelper->createUser();
        $usersToAdd[] = $profDeputyUser = $userHelper->createUser(null, User::ROLE_PROF_ADMIN);
        $usersToAdd[] = $paDeputyUser = $userHelper->createUser(null, User::ROLE_PROF_ADMIN);

        foreach ($usersToAdd as $user) {
            self::$entityManager->persist($user);
        }

        self::$entityManager->flush();

        $expectedAdminUsersReturned = [$adminUser, $adminManagerUser, $superAdminUser];
        $expectedDeputyUsersNotReturned = [$layDeputyUser, $profDeputyUser, $paDeputyUser];

        $actualAdminUsers = self::$sut->getAllAdminAccounts();

        foreach ($expectedAdminUsersReturned as $adminUser) {
            self::assertContains($adminUser, $actualAdminUsers);
        }

        foreach ($expectedDeputyUsersNotReturned as $deputyUser) {
            self::assertNotContains($deputyUser, $actualAdminUsers);
        }
    }

    public function testGetAllAdminAccountsCreatedButNotActivatedWithin()
    {
        $userHelper = UserTestHelper::create();
        $usersToAdd = [];
        $usersToAdd[] = $adminUserMoreThan60Days = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $superAdminUserMoreThan60Days = $userHelper->createUser(null, User::ROLE_SUPER_ADMIN);
        $usersToAdd[] = $adminManagerUserMoreThan60Days = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $adminUserLessThan60Days = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $nonAdminUserLessThan60Days = $userHelper->createUser(null, User::ROLE_LAY_DEPUTY);

        $adminUserMoreThan60Days->setRegistrationDate(new DateTime('-61 days'));
        $adminUserMoreThan60Days->setLastLoggedIn(null);
        $superAdminUserMoreThan60Days->setRegistrationDate(new DateTime('-61 days'));
        $superAdminUserMoreThan60Days->setLastLoggedIn(null);
        $adminManagerUserMoreThan60Days->setRegistrationDate(new DateTime('-61 days'));
        $adminManagerUserMoreThan60Days->setLastLoggedIn(null);
        $adminUserLessThan60Days->setRegistrationDate(new DateTime('-5 days'));
        $adminUserLessThan60Days->setLastLoggedIn(new DateTime());
        $nonAdminUserLessThan60Days->setRegistrationDate(new DateTime('-61 days'));
        $nonAdminUserLessThan60Days->setLastLoggedIn(null);

        foreach ($usersToAdd as $user) {
            self::$entityManager->persist($user);
        }

        self::$entityManager->flush();

        $expectedAdminUsersReturned = [$adminUserMoreThan60Days, $superAdminUserMoreThan60Days, $adminManagerUserMoreThan60Days];
        $expectedAdminUsersNotReturned = [$adminUserLessThan60Days, $nonAdminUserLessThan60Days];

        $actualAdminUsers = self::$sut->getAllAdminAccountsCreatedButNotActivatedWithin('-60 days');

        self::assertEquals($expectedAdminUsersReturned, $actualAdminUsers);

        foreach ($expectedAdminUsersNotReturned as $user) {
            self::assertNotContains($user, $actualAdminUsers);
        }
    }

    public function testGetAllActivatedAdminAccounts()
    {
        $userHelper = UserTestHelper::create();
        $usersToAdd = [];
        $usersToAdd[] = $activeAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $activeSuperAdminUser = $userHelper->createUser(null, User::ROLE_SUPER_ADMIN);
        $usersToAdd[] = $activeAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $inactiveAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $activeDeputyUser = $userHelper->createUser(null, User::ROLE_LAY_DEPUTY);

        $activeAdminUser->setLastLoggedIn(new DateTime());
        $activeSuperAdminUser->setLastLoggedIn(new DateTime());
        $activeAdminManagerUser->setLastLoggedIn(new DateTime());
        $inactiveAdminManagerUser->setLastLoggedIn();
        $activeDeputyUser->setLastLoggedIn(new DateTime());

        foreach ($usersToAdd as $user) {
            self::$entityManager->persist($user);
        }

        self::$entityManager->flush();

        $expectedActiveAdminUsersReturned = [$activeAdminUser, $activeSuperAdminUser, $activeAdminManagerUser];
        $expectedAdminUsersNotReturned = [$inactiveAdminManagerUser, $activeDeputyUser];

        $actualAdminUsers = self::$sut->getAllActivatedAdminAccounts();

        foreach ($expectedActiveAdminUsersReturned as $adminUser) {
            self::assertContains($adminUser, $actualAdminUsers);
        }

        foreach ($expectedAdminUsersNotReturned as $user) {
            self::assertNotContains($user, $actualAdminUsers);
        }
    }

    public function testGetAllAdminAccountsNotUsedWithin()
    {
        $userHelper = UserTestHelper::create();
        $usersToAdd = [];
        $usersToAdd[] = $loggedInAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $loggedInSuperAdminUser = $userHelper->createUser(null, User::ROLE_SUPER_ADMIN);
        $usersToAdd[] = $loggedInAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $recentlyLoggedInAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $recentlyLoggedInDeputyUser = $userHelper->createUser(null, User::ROLE_LAY_DEPUTY);

        $loggedInAdminUser->setLastLoggedIn(new DateTime('-95 days'));
        $loggedInSuperAdminUser->setLastLoggedIn(new DateTime('-91 days'));
        $loggedInAdminManagerUser->setLastLoggedIn(new DateTime('-91 days'));
        $recentlyLoggedInAdminManagerUser->setLastLoggedIn(new DateTime('-1 day'));
        $recentlyLoggedInDeputyUser->setLastLoggedIn(new DateTime('-1 day'));

        foreach ($usersToAdd as $user) {
            self::$entityManager->persist($user);
        }

        self::$entityManager->flush();

        $expectedLoggedInAdminUsers = [$loggedInAdminUser, $loggedInSuperAdminUser, $loggedInAdminManagerUser];
        $expectedRecentlyLoggedInUsersNotReturned = [$recentlyLoggedInAdminManagerUser, $recentlyLoggedInDeputyUser];

        $actualLoggedOutUsers = self::$sut->getAllAdminAccountsNotUsedWithin('-90 days');

        self::assertEquals($expectedLoggedInAdminUsers, $actualLoggedOutUsers);

        foreach ($expectedRecentlyLoggedInUsersNotReturned as $user) {
            self::assertNotContains($user, $actualLoggedOutUsers);
        }
    }

    public function testGetAllAdminAccountsUsedWithin()
    {
        $userHelper = UserTestHelper::create();
        $usersToAdd = [];
        $usersToAdd[] = $loggedInAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $loggedInSuperAdminUser = $userHelper->createUser(null, User::ROLE_SUPER_ADMIN);
        $usersToAdd[] = $loggedInAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $notRecentlyLoggedInAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $notRecentlyLoggedInDeputyUser = $userHelper->createUser();

        $loggedInAdminUser->setLastLoggedIn(new DateTime('-50 days'));
        $loggedInSuperAdminUser->setLastLoggedIn(new DateTime('-50 days'));
        $loggedInAdminManagerUser->setLastLoggedIn(new DateTime('-50 days'));
        $notRecentlyLoggedInAdminManagerUser->setLastLoggedIn(new DateTime('-100 days'));
        $notRecentlyLoggedInDeputyUser->setLastLoggedIn(new DateTime('-100 days'));

        foreach ($usersToAdd as $user) {
            self::$entityManager->persist($user);
        }

        self::$entityManager->flush();

        $expectedLoggedInAdminUsers = [$loggedInAdminUser, $loggedInSuperAdminUser, $loggedInAdminManagerUser];
        $expectedLoggedOutUsersNotReturned = [$notRecentlyLoggedInAdminManagerUser, $notRecentlyLoggedInDeputyUser];

        $actualLoggedInUsers = self::$sut->getAllAdminAccountsUsedWithin('-90 days');

        foreach ($expectedLoggedInAdminUsers as $adminUser) {
            self::assertContains($adminUser, $actualLoggedInUsers);
        }

        foreach ($expectedLoggedOutUsersNotReturned as $user) {
            self::assertNotContains($user, $actualLoggedInUsers);
        }
    }

    public function testGetAllAdminUserAccountsNotUsedWithin()
    {
        $userHelper = UserTestHelper::create();
        $usersToAdd = [];
        $usersToAdd[] = $notRecentlyLoggedInAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $notRecentlyLoggedInSuperAdminManagerUser = $userHelper->createUser(null, User::ROLE_SUPER_ADMIN);
        $usersToAdd[] = $recentlyLoggedInAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $recentlyLoggedInAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER);
        $usersToAdd[] = $recentlyLoggedInSuperAdminManagerUser = $userHelper->createUser(null, User::ROLE_SUPER_ADMIN);
        $usersToAdd[] = $recentlyLoggedInDeputyUser = $userHelper->createUser(null, User::ROLE_LAY_DEPUTY);

        $notRecentlyLoggedInAdminUser->setLastLoggedIn(new DateTime('-13 months'));
        $notRecentlyLoggedInSuperAdminManagerUser->setLastLoggedIn(new DateTime('-24 months'));
        $recentlyLoggedInAdminUser->setLastLoggedIn(new DateTime('-10 days'));
        $recentlyLoggedInAdminManagerUser->setLastLoggedIn(new DateTime('-10 days'));
        $recentlyLoggedInSuperAdminManagerUser->setLastLoggedIn(new DateTime('-10 days'));

        $recentlyLoggedInDeputyUser->setLastLoggedIn(new DateTime('-10 days'));

        foreach ($usersToAdd as $user) {
            self::$entityManager->persist($user);
        }

        self::$entityManager->flush();

        $expectedLoggedInAdminUsers = [$notRecentlyLoggedInAdminUser, $notRecentlyLoggedInSuperAdminManagerUser];
        $expectedRecentlyLoggedInUsersNotReturned = [$recentlyLoggedInAdminUser, $recentlyLoggedInAdminManagerUser, $recentlyLoggedInDeputyUser];

        $actualLoggedInAdminUsers = self::$sut->getAllAdminAccountsNotUsedWithin('-12 months');

        self::assertEquals($expectedLoggedInAdminUsers, $actualLoggedInAdminUsers);

        foreach ($expectedRecentlyLoggedInUsersNotReturned as $user) {
            self::assertNotContains($user, $actualLoggedInAdminUsers);
        }
    }

    public function testInactiveAdminUsersAreDeleted()
    {
        $userHelper = UserTestHelper::create();

        $usersToAdd = [];
        $usersToAdd[] = $activeAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN)
            ->setLastLoggedIn(new DateTime('-2 months'));
        $usersToAdd[] = $activeLayDeputyUser = $userHelper->createUser(null, User::ROLE_LAY_DEPUTY)
            ->setLastLoggedIn(new DateTime('-2 months'));
        $usersToAdd[] = $inactiveAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN)
            ->setLastLoggedIn(new DateTime('-25 months'));
        $usersToAdd[] = $inactiveAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER)
            ->setLastLoggedIn(new DateTime('-26 months'));

        foreach ($usersToAdd as $user) {
            self::$entityManager->persist($user);
        }

        self::$entityManager->flush();

        $adminUserIds = [];
        foreach ($usersToAdd as $user) {
            $adminUserIds[] = $user->getId();
        }

        self::$sut->deleteInactiveAdminUsers($adminUserIds);

        $adminUsersDeleted = [$inactiveAdminUser->getId(), $inactiveAdminManagerUser->getId()];
        $usersNotDeleted = [$activeAdminUser->getId(), $activeLayDeputyUser->getId()];

        $deletedAdminUsers = self::$sut->findBy(['id' => $adminUsersDeleted]);
        $this->assertCount(0, $deletedAdminUsers);

        $usersNotDeleted = self::$sut->findBy(['id' => $usersNotDeleted]);
        $this->assertCount(2, $usersNotDeleted);
    }

    public function testFindUsersWithoutDeputies(): void
    {
        $userHelper = UserTestHelper::create();

        // two users without deputies
        $user1 = $userHelper->createUser();
        self::$entityManager->persist($user1);

        $user2 = $userHelper->createUser();
        self::$entityManager->persist($user2);

        // one user with a deputy - should not be returned
        $user3 = $userHelper->createUser();
        self::$entityManager->persist($user3);

        $deputy = DeputyTestHelper::generateDeputy(user: $user3);
        self::$entityManager->persist($deputy);

        // corresponding pre_registration entries for the users
        $preReg1 = new PreRegistration(['DeputyUid' => "{$user1->getDeputyUid()}"]);
        self::$entityManager->persist($preReg1);

        $preReg2 = new PreRegistration(['DeputyUid' => "{$user2->getDeputyUid()}"]);
        self::$entityManager->persist($preReg2);

        $preReg3 = new PreRegistration(['DeputyUid' => "{$user3->getDeputyUid()}"]);
        self::$entityManager->persist($preReg3);

        self::$entityManager->flush();

        // test
        $foundUsers = iterator_to_array(self::$sut->findUsersWithoutDeputies());

        self::assertCount(2, $foundUsers);
        self::assertContains($user1, $foundUsers);
        self::assertContains($user2, $foundUsers);
        self::assertNotContains($user3, $foundUsers);
    }
}
