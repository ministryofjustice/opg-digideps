<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\Client\Internal;

use Faker\Factory;
use Faker\Generator;
use OPG\Digideps\Common\Registration\SelfRegisterData;
use OPG\Digideps\Frontend\Entity\User;
use OPG\Digideps\Frontend\Event\AdminManagerCreatedEvent;
use OPG\Digideps\Frontend\Event\AdminUserCreatedEvent;
use OPG\Digideps\Frontend\Event\CoDeputyInvitedEvent;
use OPG\Digideps\Frontend\Event\DeputyInvitedEvent;
use OPG\Digideps\Frontend\Event\DeputySelfRegisteredEvent;
use OPG\Digideps\Frontend\Event\OrgUserCreatedEvent;
use OPG\Digideps\Frontend\Event\UserDeletedEvent;
use OPG\Digideps\Frontend\Event\UserPasswordResetEvent;
use OPG\Digideps\Frontend\Event\UserUpdatedEvent;
use OPG\Digideps\Frontend\EventDispatcher\ObservableEventDispatcher;
use OPG\Digideps\Frontend\Service\Client\Internal\UserApi;
use OPG\Digideps\Frontend\Service\Client\RestClient;
use OPG\Digideps\Frontend\TestHelpers\UserHelpers;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Contracts\EventDispatcher\Event;

class UserApiTest extends TestCase
{
    private RestClient&MockObject $restClient;
    private TokenStorageInterface&MockObject $tokenStorage;
    private ObservableEventDispatcher&MockObject $eventDispatcher;
    private Generator $faker;
    private UserApi $sut;

    public function setUp(): void
    {
        $this->restClient = self::createMock(RestClient::class);
        $this->tokenStorage = self::createMock(TokenStorageInterface::class);
        $this->eventDispatcher = self::createMock(ObservableEventDispatcher::class);
        $this->faker = Factory::create();

        $this->sut = new UserApi(
            $this->restClient,
            $this->tokenStorage,
            $this->eventDispatcher
        );
    }

    public function testUpdate(): void
    {
        $preUpdateUser = UserHelpers::createUser();
        $postUpdateUser = UserHelpers::createUser();
        $currentUser = UserHelpers::createUser();
        $trigger = 'SOME_TRIGGER';
        $jmsGroups = ['group1'];

        $this->restClient->expects(self::once())
            ->method('put')
            ->with(sprintf('user/%d', $preUpdateUser->getId()), $postUpdateUser, $jmsGroups);

        $usernamePasswordToken = new UsernamePasswordToken($currentUser, 'firewall', $currentUser->getRoles());
        $this->tokenStorage->expects(self::once())->method('getToken')->willReturn($usernamePasswordToken);

        $userUpdatedEvent = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->eventDispatcher->expects(self::once())->method('dispatch')->with($userUpdatedEvent, 'user.updated');

        $this->sut->update($preUpdateUser, $postUpdateUser, $trigger, $jmsGroups);
    }

    public function testDelete(): void
    {
        $userToDelete = UserHelpers::createUser();
        $deletedBy = UserHelpers::createUser();
        $trigger = 'SOME_TRIGGER';

        $this->restClient->delete(sprintf('user/%s', $userToDelete->getId()));

        $usernamePasswordToken = new UsernamePasswordToken($deletedBy, 'firewall', $deletedBy->getRoles());
        $this->tokenStorage->expects(self::once())->method('getToken')->willReturn($usernamePasswordToken);

        $userUpdatedEvent = new UserDeletedEvent($userToDelete, $deletedBy, $trigger);
        $this->eventDispatcher->expects(self::once())->method('dispatch')->with($userUpdatedEvent, 'user.deleted');

        $this->sut->delete($userToDelete, $trigger);
    }

    public function testCreateAdminUser(): void
    {
        $userToCreate = UserHelpers::createUser();

        $this->restClient->expects(self::once())
            ->method('post')
            ->with('user', $userToCreate, ['admin_add_user'], 'User')
            ->willReturn($userToCreate);

        $userCreatedEvent = new AdminUserCreatedEvent($userToCreate);
        $this->eventDispatcher->expects(self::once())->method('dispatch')->with($userCreatedEvent, 'admin.user.created');

        $this->sut->createUser($userToCreate);
    }

    public function testCreateAdminManagerUser(): void
    {
        $currentUser = UserHelpers::createSuperAdminUser();
        $userToCreate = UserHelpers::createAdminManager();

        $trigger = 'ADMIN_MANAGER_MANUALLY_CREATED';

        $usernamePasswordToken = new UsernamePasswordToken($currentUser, 'firewall', $currentUser->getRoles());
        $this->tokenStorage->expects(self::once())
            ->method('getToken')
            ->willReturn($usernamePasswordToken);

        $this->restClient->expects(self::once())
            ->method('post')
            ->with('user', $userToCreate, ['admin_add_user'], 'User')
            ->willReturn($userToCreate);

        $invocationMatcher = self::exactly(2);
        $expected = [
            1 => [new AdminUserCreatedEvent($userToCreate), 'admin.user.created'],
            2 => [new AdminManagerCreatedEvent($trigger, $currentUser, $userToCreate), 'admin.manager.created'],
        ];

        $this->eventDispatcher->expects($invocationMatcher)
            ->method('dispatch')
            ->willReturnCallback(function (Event $event, string $message) use ($invocationMatcher, $expected) {
                $invocation = $invocationMatcher->getInvocationCount();
                self::assertInstanceOf(get_class($expected[$invocation][0]), $event);
                self::assertEquals($expected[$invocation][0], $event);
                self::assertEquals($expected[$invocation][1], $message);
            });

        $this->sut->createUser($userToCreate);
    }

    public function testResetPassword(): void
    {
        $userToResetPassword = UserHelpers::createUser();
        $email = $this->faker->safeEmail();

        $this->restClient->expects(self::once())
            ->method('apiCall')
            ->with('put', 'user/recreate-token/' . $email, null, User::class, [], false)
            ->willReturn($userToResetPassword);

        $passwordResetEvent = new UserPasswordResetEvent($userToResetPassword);
        $this->eventDispatcher->expects(self::once())->method('dispatch')->with($passwordResetEvent, 'password.reset');

        $this->sut->resetPassword($email);
    }

    public function testReInviteCoDeputy(): void
    {
        $invitedCoDeputy = UserHelpers::createUser();
        $inviterDeputy = UserHelpers::createUser();
        $email = $this->faker->safeEmail();

        $this->restClient->expects(self::once())
            ->method('apiCall')
            ->with('put', 'user/recreate-token/' . $email, null, User::class, [], false)
            ->willReturn($invitedCoDeputy);

        $coDeputyInvitedEvent = new CoDeputyInvitedEvent($invitedCoDeputy, $inviterDeputy);
        $this->eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with($coDeputyInvitedEvent, 'codeputy.invited');

        $this->sut->reInviteCoDeputy($email, $inviterDeputy);
    }

    public function testGetByEmail(): void
    {
        $existingUser = UserHelpers::createUser();

        $this->restClient->expects(self::once())
            ->method('get')
            ->with(sprintf('user/get-one-by/email/%s', $existingUser->getEmail()), 'User', [])
            ->willReturn($existingUser);

        $returnedUser = $this->sut->getByEmail($existingUser->getEmail());

        self::assertEquals($existingUser, $returnedUser);
    }

    public function testReInviteDeputy(): void
    {
        $invitedDeputy = UserHelpers::createUser();
        $email = $this->faker->safeEmail();

        $this->restClient->expects(self::once())
            ->method('apiCall')
            ->with('put', 'user/recreate-token/' . $email, null, User::class, [], false)
            ->willReturn($invitedDeputy);

        $deputyInvitedEvent = new DeputyInvitedEvent($invitedDeputy);
        $this->eventDispatcher->expects(self::once())->method('dispatch')->with($deputyInvitedEvent, 'deputy.invited');

        $this->sut->reInviteDeputy($email);
    }

    public function testSelfRegister(): void
    {
        $selfRegisteredDeputy = UserHelpers::createUser();
        $selfRegisterData = new SelfRegisterData()
            ->setFirstname('Denis')
            ->setLastname('Brauchla')
            ->setPostcode('DB1 9FI')
            ->setEmail('d.brauchla@mailbox.example')
            ->setClientFirstname('Abraham')
            ->setClientLastname('Ruhter')
            ->setCaseNumber('13859388');

        $this->restClient->expects(self::once())
            ->method('apiCall')
            ->with('post', 'selfregister', $selfRegisterData, User::class, [], false)
            ->willReturn($selfRegisteredDeputy);

        $deputySelfRegisteredEvent = new DeputySelfRegisteredEvent($selfRegisteredDeputy);
        $this->eventDispatcher->expects(self::once())
            ->method('dispatch')
            ->with($deputySelfRegisteredEvent, 'deputy.self.registered');

        $this->sut->selfRegister($selfRegisterData);
    }

    public function testCreateOrgUser(): void
    {
        $userToCreate = UserHelpers::createUser();

        $this->restClient->expects(self::once())
            ->method('post')
            ->with('user', $userToCreate, ['org_team_add'], 'User')
            ->willReturn($userToCreate);

        $userCreatedEvent = new OrgUserCreatedEvent($userToCreate);
        $this->eventDispatcher->expects(self::once())->method('dispatch')->with($userCreatedEvent, 'org.user.created');

        $this->sut->createOrgUser($userToCreate);
    }

    public function testReturnPrimaryEmailNullDeputyUid(): void
    {
        // null deputy UID returns null
        self::assertNull($this->sut->returnPrimaryEmail(null));
    }

    public function testReturnPrimaryEmailDeputyNotFound(): void
    {
        // non-null deputy UID, but deputy not found
        $deputyUid = 77777777;
        $this->restClient->expects(self::once())
            ->method('get')
            ->with('user/get-primary-email/' . $deputyUid, 'raw')
            ->willReturn('{"data": null}');

        $result = $this->sut->returnPrimaryEmail($deputyUid);

        self::assertNull($result);
    }

    public function testReturnPrimaryEmail(): void
    {
        $expectedEmail = 'fakeemail@nowhere.biz.uk';

        // non-null deputy UID, deputy found
        $deputyUid = 77777777;
        $this->restClient->expects(self::once())
            ->method('get')
            ->with('user/get-primary-email/' . $deputyUid, 'raw')
            ->willReturn("{\"data\": \"$expectedEmail\"}");

        $result = $this->sut->returnPrimaryEmail($deputyUid);

        self::assertEquals($expectedEmail, $result);
    }
}
