<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Unit\Command;

use OPG\Digideps\Backend\Command\UserRetentionPolicyCommand;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Event\UserRetentionPolicyCommandEvent;
use OPG\Digideps\Backend\EventDispatcher\ObservableEventDispatcher;
use OPG\Digideps\Backend\Repository\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class UserRetentionPolicyCommandTest extends KernelTestCase
{
    private UserRepository&MockObject $userRepository;
    private ObservableEventDispatcher&MockObject $eventDispatcher;
    private LoggerInterface&MockObject $logger;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $kernel = self::createKernel();
        $app = new Application($kernel);

        $this->userRepository = self::createMock(UserRepository::class);
        $this->eventDispatcher = self::createMock(ObservableEventDispatcher::class);
        $this->logger = self::createMock(LoggerInterface::class);

        $sut = new UserRetentionPolicyCommand(
            $this->userRepository,
            $this->eventDispatcher,
            $this->logger
        );

        $app->add($sut);

        $command = $app->find(UserRetentionPolicyCommand::$defaultName);
        $this->commandTester = new CommandTester($command);
    }

    public function testOutputWithNoInactiveAdminUsers(): void
    {
        $this->userRepository->expects(self::once())
            ->method('getAllAdminAccountsNotUsedWithin')
            ->with('-24 months')
            ->willReturn(null);

        $this->userRepository->expects(self::once())
            ->method('getAllDeletionProtectedAccounts')
            ->willReturn([1, 2, 3, 4, 5, 6]);

        $result = $this->commandTester->execute([]);
        $this->assertEquals(0, $result);
    }

    public function testExecuteWithInactiveAdminUser(): void
    {
        $user = new User();
        $user->setRoleName(User::ROLE_ADMIN);
        $user->setId(1);
        $user->setLastLoggedIn(new \DateTime('-36 months'));

        $this->userRepository->expects(self::once())
            ->method('getAllAdminAccountsNotUsedWithin')
            ->with('-24 months')
            ->willReturn($user);

        $this->userRepository->expects(self::once())
            ->method('getAllDeletionProtectedAccounts')
            ->willReturn([2, 3, 4, 5, 6]);

        $trigger = 'USER_DELETED_AUTOMATION';
        $userRetentionEvent = new UserRetentionPolicyCommandEvent($user, $trigger);

        $this->eventDispatcher->dispatch($userRetentionEvent, 'user.deleted');
        $this->logger->notice('Deleted user account with id: 1 at admin permission level due to 2 year expiry.');

        $this->userRepository->expects(self::once())
            ->method('deleteInactiveAdminUsers')
            ->with([1]);

        $result = $this->commandTester->execute([]);
        $this->assertEquals(0, $result);
    }

    public function testExecuteWithInactiveAdminUsers(): void
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

        $this->userRepository->expects(self::once())
            ->method('getAllAdminAccountsNotUsedWithin')
            ->with('-24 months')
            ->willReturn($expectedInactiveAdminUsersReturned);

        $this->userRepository->expects(self::once())
            ->method('getAllDeletionProtectedAccounts')
            ->willReturn([4]);

        $trigger = 'USER_DELETED_AUTOMATION';

        $userRetentionEvent = new UserRetentionPolicyCommandEvent($inactiveAdminUser, $trigger);
        $this->eventDispatcher->dispatch($userRetentionEvent, 'user.deleted');
        $this->logger->notice('Deleted user account with id: 1 at admin permission level due to 2 year expiry.');

        $userRetentionEvent = new UserRetentionPolicyCommandEvent($inactiveAdminManagerUser, $trigger);
        $this->eventDispatcher->dispatch($userRetentionEvent, 'user.deleted');
        $this->logger->notice('Deleted user account with id: 2 at admin permission level due to 2 year expiry.');

        $this->userRepository->expects(self::once())
            ->method('deleteInactiveAdminUsers')
            ->with([1, 2]);

        $result = $this->commandTester->execute([]);
        $this->assertEquals(0, $result);
    }
}
