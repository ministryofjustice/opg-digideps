<?php

namespace Tests\OPG\Digideps\Backend\Integration\Entity\Repository;

use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\ReportList;
use OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\ApiTestTrait;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Repository\UserRepository;
use OPG\Digideps\Backend\TestHelpers\DeputyTestHelper;
use OPG\Digideps\Backend\TestHelpers\UserTestHelper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    use ApiTestTrait;

    private static UserRepository $sut;

    public function setUp(): void
    {
        parent::setUp();

        self::configureTest();

        /** @var UserRepository $sut */
        $sut = self::$entityManager->getRepository(User::class);

        self::$sut = $sut;

        self::purgeDatabase();
    }

    public function testFindActiveLaysInLastYear()
    {
        $scenario = new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::manyReports()));
        ['persons' => ['users' => ['lay1' => $activeUserOne]]] = self::$fixtureService->instantiateScenario($scenario, flush: false);
        ['persons' => ['users' => ['lay1' => $activeUserTwo]]] = self::$fixtureService->instantiateScenario($scenario, flush: false);
        ['persons' => ['users' => ['lay1' => $inactiveUserOne]]] = self::$fixtureService->instantiateScenario($scenario, flush: false);
        $inactiveUserOne->setLastLoggedIn(new \DateTime('-380 days'));
        ['persons' => ['users' => ['lay1' => $inactiveUserTwo]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario(), flush: false);

        self::$fixtureService->flush();

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
        $usersToAdd[] = $adminUserMoreThan60Days = $userHelper->createUser(null, User::ROLE_ADMIN, active: false);
        $usersToAdd[] = $superAdminUserMoreThan60Days = $userHelper->createUser(null, User::ROLE_SUPER_ADMIN, active: false);
        $usersToAdd[] = $adminManagerUserMoreThan60Days = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER, active: false);
        $usersToAdd[] = $adminUserLessThan60Days = $userHelper->createUser(null, User::ROLE_ADMIN);
        $usersToAdd[] = $nonAdminUserLessThan60Days = $userHelper->createUser(active: false);

        $adminUserMoreThan60Days->setRegistrationDate(new \DateTime('-61 days'));
        $superAdminUserMoreThan60Days->setRegistrationDate(new \DateTime('-61 days'));
        $adminManagerUserMoreThan60Days->setRegistrationDate(new \DateTime('-61 days'));
        $adminUserLessThan60Days->setRegistrationDate(new \DateTime('-5 days'));
        $adminUserLessThan60Days->setLastLoggedIn(new \DateTime());
        $nonAdminUserLessThan60Days->setRegistrationDate(new \DateTime('-61 days'));

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
        $usersToAdd[] = $inactiveAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER, active: false);
        $usersToAdd[] = $activeDeputyUser = $userHelper->createUser();

        $activeAdminUser->setLastLoggedIn(new \DateTime());
        $activeSuperAdminUser->setLastLoggedIn(new \DateTime());
        $activeAdminManagerUser->setLastLoggedIn(new \DateTime());
        $activeDeputyUser->setLastLoggedIn(new \DateTime());

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
        $usersToAdd[] = $recentlyLoggedInDeputyUser = $userHelper->createUser();

        $loggedInAdminUser->setLastLoggedIn(new \DateTime('-95 days'));
        $loggedInSuperAdminUser->setLastLoggedIn(new \DateTime('-91 days'));
        $loggedInAdminManagerUser->setLastLoggedIn(new \DateTime('-91 days'));
        $recentlyLoggedInAdminManagerUser->setLastLoggedIn(new \DateTime('-1 day'));
        $recentlyLoggedInDeputyUser->setLastLoggedIn(new \DateTime('-1 day'));

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

        $loggedInAdminUser->setLastLoggedIn(new \DateTime('-50 days'));
        $loggedInSuperAdminUser->setLastLoggedIn(new \DateTime('-50 days'));
        $loggedInAdminManagerUser->setLastLoggedIn(new \DateTime('-50 days'));
        $notRecentlyLoggedInAdminManagerUser->setLastLoggedIn(new \DateTime('-100 days'));
        $notRecentlyLoggedInDeputyUser->setLastLoggedIn(new \DateTime('-100 days'));

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
        $usersToAdd[] = $recentlyLoggedInDeputyUser = $userHelper->createUser();

        $notRecentlyLoggedInAdminUser->setLastLoggedIn(new \DateTime('-13 months'));
        $notRecentlyLoggedInSuperAdminManagerUser->setLastLoggedIn(new \DateTime('-24 months'));
        $recentlyLoggedInAdminUser->setLastLoggedIn(new \DateTime('-10 days'));
        $recentlyLoggedInAdminManagerUser->setLastLoggedIn(new \DateTime('-10 days'));
        $recentlyLoggedInSuperAdminManagerUser->setLastLoggedIn(new \DateTime('-10 days'));

        $recentlyLoggedInDeputyUser->setLastLoggedIn(new \DateTime('-10 days'));

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
            ->setLastLoggedIn(new \DateTime('-2 months'));
        $usersToAdd[] = $activeLayDeputyUser = $userHelper->createUser()
            ->setLastLoggedIn(new \DateTime('-2 months'));
        $usersToAdd[] = $inactiveAdminUser = $userHelper->createUser(null, User::ROLE_ADMIN)
            ->setLastLoggedIn(new \DateTime('-25 months'));
        $usersToAdd[] = $inactiveAdminManagerUser = $userHelper->createUser(null, User::ROLE_ADMIN_MANAGER)
            ->setLastLoggedIn(new \DateTime('-26 months'));

        foreach ($usersToAdd as $user) {
            /** @var User $user */
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

        self::$entityManager->flush();

        // test
        $foundUsers = iterator_to_array(self::$sut->findUsersWithoutDeputies());

        self::assertCount(2, $foundUsers);
        self::assertContains($user1, $foundUsers);
        self::assertContains($user2, $foundUsers);
        self::assertNotContains($user3, $foundUsers);
    }
}
