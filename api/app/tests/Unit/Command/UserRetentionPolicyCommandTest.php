<?php

namespace App\Tests\Unit\Command;

use App\Command\UserRetentionPolicyCommand;
use App\Entity\User;
use App\Event\UserRetentionPolicyCommandEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Repository\UserRepository;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UserRetentionPolicyCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        $this->userRepository = self::prophesize(UserRepository::class);
        $this->eventDispatcher = self::prophesize(ObservableEventDispatcher::class);
        $this->logger = self::prophesize(LoggerInterface::class);

        $sut = new UserRetentionPolicyCommand(
            $this->userRepository->reveal(),
            $this->eventDispatcher->reveal(),
            $this->logger->reveal()
        );

        $app->add($sut);

        $command = $app->find(UserRetentionPolicyCommand::$defaultName);
        $this->commandTester = new CommandTester($command);
    }

    /**
     * @test
     */
    public function testOutputWithNoInactiveAdminUsers()
    {
        $this->userRepository->getAllAdminAccountsNotUsedWithin('-24 months')
            ->shouldBeCalled()
            ->willReturn(null);

        $this->userRepository->getAllDeletionProtectedAccounts()
            ->shouldBeCalled()
            ->willReturn([1, 2, 3, 4, 5, 6]);

        $result = $this->commandTester->execute([]);
        $this->assertEquals(0, $result);
    }

    /**
     * @test
     */
    public function testExecuteWithInactiveAdminUser()
    {
        $user = new User();
        $user->setRoleName(User::ROLE_ADMIN);
        $user->setId(1);
        $user->setLastLoggedIn(new \DateTime('-36 months'));

        $this->userRepository->getAllAdminAccountsNotUsedWithin('-24 months')
            ->shouldBeCalled()
            ->willReturn($user);

        $this->userRepository->getAllDeletionProtectedAccounts()
            ->shouldBeCalled()
            ->willReturn([2, 3, 4, 5, 6]);

        $trigger = 'USER_DELETED_AUTOMATION';
        $userRetentionEvent = new UserRetentionPolicyCommandEvent($user, $trigger);

        $this->eventDispatcher->dispatch($userRetentionEvent, 'user.deleted')->shouldBeCalled();
        $this->logger->notice('Deleted user account with id: 1 at admin permission level due to 2 year expiry.')->shouldBeCalled();

        $this->userRepository->deleteInactiveAdminUsers([1])->shouldBeCalled();

        $result = $this->commandTester->execute([]);
        $this->assertEquals(0, $result);
    }

    /**
     * @test
     */
    public function testExecuteWithInactiveAdminUsers()
    {
        $inactiveAdminUser = new User();
        $inactiveAdminUser->setRoleName(User::ROLE_ADMIN);
        $inactiveAdminUser->setId(1);
        $inactiveAdminUser->setLastLoggedIn(new \DateTime('-36 months'));

        $inactiveAdminManagerUser = new User();
        $inactiveAdminManagerUser->setRoleName(User::ROLE_ADMIN_MANAGER);
        $inactiveAdminManagerUser->setId(2);
        $inactiveAdminManagerUser->setLastLoggedIn(new \DateTime('-30 months'));

        $activeSuperAdmin = new User();
        $activeSuperAdmin->setRoleName(User::ROLE_SUPER_ADMIN);
        $activeSuperAdmin->setId(3);
        $activeSuperAdmin->setLastLoggedIn(new \DateTime('-2 months'));

        $inactiveSuperAdminProtected = new User();
        $inactiveSuperAdminProtected->setRoleName(User::ROLE_SUPER_ADMIN);
        $inactiveSuperAdminProtected->setId(4);
        $inactiveSuperAdminProtected->setLastLoggedIn(new \DateTime('-26 months'));

        $expectedInactiveAdminUsersReturned = [
            $inactiveAdminUser,
            $inactiveAdminManagerUser,
            $inactiveSuperAdminProtected,
        ];

        $this->userRepository->getAllAdminAccountsNotUsedWithin('-24 months')
            ->shouldBeCalled()
            ->willReturn($expectedInactiveAdminUsersReturned);

        $this->userRepository->getAllDeletionProtectedAccounts()
            ->shouldBeCalled()
            ->willReturn([4]);

        $trigger = 'USER_DELETED_AUTOMATION';

        $userRetentionEvent = new UserRetentionPolicyCommandEvent($inactiveAdminUser, $trigger);
        $this->eventDispatcher->dispatch($userRetentionEvent, 'user.deleted')->shouldBeCalled();
        $this->logger->notice('Deleted user account with id: 1 at admin permission level due to 2 year expiry.')->shouldBeCalled();

        $userRetentionEvent = new UserRetentionPolicyCommandEvent($inactiveAdminManagerUser, $trigger);
        $this->eventDispatcher->dispatch($userRetentionEvent, 'user.deleted')->shouldBeCalled();
        $this->logger->notice('Deleted user account with id: 2 at admin permission level due to 2 year expiry.')->shouldBeCalled();

        $this->userRepository->deleteInactiveAdminUsers([1, 2])->shouldBeCalled();

        $result = $this->commandTester->execute([]);
        $this->assertEquals(0, $result);
    }
}
