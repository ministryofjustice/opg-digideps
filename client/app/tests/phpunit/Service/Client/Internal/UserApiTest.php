<?php

declare(strict_types=1);

use App\Event\AdminManagerCreatedEvent;
use App\Event\AdminUserCreatedEvent;
use App\Event\CoDeputyCreatedEvent;
use App\Event\CoDeputyInvitedEvent;
use App\Event\DeputyInvitedEvent;
use App\Event\DeputySelfRegisteredEvent;
use App\Event\OrgUserCreatedEvent;
use App\Event\UserDeletedEvent;
use App\Event\UserPasswordResetEvent;
use App\Event\UserUpdatedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Model\SelfRegisterData;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\TestHelpers\UserHelpers;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserApiTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy */
    private $restClient;

    /** @var ObjectProphecy */
    private $tokenStorage;

    /** @var ObjectProphecy */
    private $eventDispatcher;

    /** @var UserApi */
    private $sut;

    /** @var Faker\Generator */
    private $faker;

    public function setUp(): void
    {
        $this->restClient = self::prophesize(RestClient::class);
        $this->tokenStorage = self::prophesize(TokenStorageInterface::class);
        $this->eventDispatcher = self::prophesize(ObservableEventDispatcher::class);
        $this->faker = Factory::create();

        $this->sut = new UserApi(
            $this->restClient->reveal(),
            $this->tokenStorage->reveal(),
            $this->eventDispatcher->reveal()
        );
    }

    /** @test */
    public function update()
    {
        $preUpdateUser = UserHelpers::createUser();
        $postUpdateUser = UserHelpers::createUser();
        $currentUser = UserHelpers::createUser();
        $trigger = 'SOME_TRIGGER';
        $jmsGroups = ['group1'];

        $this->restClient->put(sprintf('user/%s', $preUpdateUser->getId()), $postUpdateUser, $jmsGroups)->shouldBeCalled();

        $usernamePasswordToken = new UsernamePasswordToken($currentUser, 'firewall', $currentUser->getRoles());
        $this->tokenStorage->getToken()->willReturn($usernamePasswordToken);

        $userUpdatedEvent = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->eventDispatcher->dispatch($userUpdatedEvent, 'user.updated')->shouldBeCalled();

        $this->sut->update($preUpdateUser, $postUpdateUser, $trigger, $jmsGroups);
    }

    /** @test */
    public function delete()
    {
        $userToDelete = UserHelpers::createUser();
        $deletedBy = UserHelpers::createUser();
        $trigger = 'SOME_TRIGGER';

        $this->restClient->delete(sprintf('user/%s', $userToDelete->getId()))->shouldBeCalled();

        $usernamePasswordToken = new UsernamePasswordToken($deletedBy, 'firewall', $deletedBy->getRoles());
        $this->tokenStorage->getToken()->willReturn($usernamePasswordToken);

        $userUpdatedEvent = new UserDeletedEvent($userToDelete, $deletedBy, $trigger);
        $this->eventDispatcher->dispatch($userUpdatedEvent, 'user.deleted')->shouldBeCalled();

        $this->sut->delete($userToDelete, $trigger);
    }

    /** @test */
    public function createAdminUser()
    {
        $userToCreate = UserHelpers::createUser();

        $this->restClient->post('user', $userToCreate, ['admin_add_user'], 'User')->shouldBeCalled()->willReturn($userToCreate);

        $userCreatedEvent = new AdminUserCreatedEvent($userToCreate);
        $this->eventDispatcher->dispatch($userCreatedEvent, 'admin.user.created')->shouldBeCalled();

        $this->sut->createUser($userToCreate);
    }

    /** @test */
    public function createAdminManagerUser()
    {
        $currentUser = UserHelpers::createSuperAdminUser();
        $userToCreate = UserHelpers::createAdminManager();

        $trigger = 'ADMIN_MANAGER_MANUALLY_CREATED';

        $usernamePasswordToken = new UsernamePasswordToken($currentUser, 'firewall', $currentUser->getRoles());
        $this->tokenStorage->getToken()->willReturn($usernamePasswordToken);

        $this->restClient->post('user', $userToCreate, ['admin_add_user'], 'User')->shouldBeCalled()->willReturn($userToCreate);

        $userCreatedEvent = new AdminUserCreatedEvent($userToCreate);
        $this->eventDispatcher->dispatch($userCreatedEvent, 'admin.user.created')->shouldBeCalled();

        $adminManagerCreatedEvent = new AdminManagerCreatedEvent($trigger, $currentUser, $userToCreate);
        $this->eventDispatcher->dispatch($adminManagerCreatedEvent, 'admin.manager.created')->shouldBeCalled();

        $this->sut->createUser($userToCreate);
    }

    /** @test */
    public function resetPassword()
    {
        $userToResetPassword = UserHelpers::createUser();
        $email = $this->faker->safeEmail();

        $this->restClient
            ->apiCall('put', sprintf('user/recreate-token/%s', $email), null, 'User', [], false)
            ->shouldBeCalled()
            ->willReturn($userToResetPassword);

        $passwordResetEvent = new UserPasswordResetEvent($userToResetPassword);
        $this->eventDispatcher->dispatch($passwordResetEvent, 'password.reset')->shouldBeCalled();

        $this->sut->resetPassword($email);
    }

    /** @test */
    public function reInviteCoDeputy()
    {
        $invitedCoDeputy = UserHelpers::createUser();
        $inviterDeputy = UserHelpers::createUser();
        $email = $this->faker->safeEmail();

        $this->restClient
            ->apiCall('put', sprintf('user/recreate-token/%s', $email), null, 'User', [], false)
            ->shouldBeCalled()
            ->willReturn($invitedCoDeputy);

        $coDeputyInvitedEvent = new CoDeputyInvitedEvent($invitedCoDeputy, $inviterDeputy);
        $this->eventDispatcher->dispatch($coDeputyInvitedEvent, 'codeputy.invited')->shouldBeCalled();

        $this->sut->reInviteCoDeputy($email, $inviterDeputy);
    }

    /** @test */
    public function getByEmail()
    {
        $existingUser = UserHelpers::createUser();

        $this->restClient
            ->get(sprintf('user/get-one-by/email/%s', $existingUser->getEmail()), 'User', [])
            ->shouldBeCalled()
            ->willReturn($existingUser);

        $returnedUser = $this->sut->getByEmail($existingUser->getEmail());

        self::assertEquals($existingUser, $returnedUser);
    }

    /** @test */
    public function reInviteDeputy()
    {
        $invitedDeputy = UserHelpers::createUser();
        $email = $this->faker->safeEmail();

        $this->restClient
            ->apiCall('put', sprintf('user/recreate-token/%s', $email), null, 'User', [], false)
            ->shouldBeCalled()
            ->willReturn($invitedDeputy);

        $deputyInvitedEvent = new DeputyInvitedEvent($invitedDeputy);
        $this->eventDispatcher->dispatch($deputyInvitedEvent, 'deputy.invited')->shouldBeCalled();

        $this->sut->reInviteDeputy($email);
    }

    /** @test */
    public function selfRegister()
    {
        $selfRegisteredDeputy = UserHelpers::createUser();
        $selfRegisterData = (new SelfRegisterData())
            ->setFirstname('Denis')
            ->setLastname('Brauchla')
            ->setPostcode('DB1 9FI')
            ->setEmail('d.brauchla@mailbox.example')
            ->setClientFirstname('Abraham')
            ->setClientLastname('Ruhter')
            ->setCaseNumber('13859388');

        $this->restClient
            ->apiCall('post', 'selfregister', $selfRegisterData, 'User', [], false)
            ->shouldBeCalled()
            ->willReturn($selfRegisteredDeputy);

        $deputySelfRegisteredEvent = new DeputySelfRegisteredEvent($selfRegisteredDeputy);
        $this->eventDispatcher->dispatch($deputySelfRegisteredEvent, 'deputy.self.registered')->shouldBeCalled();

        $this->sut->selfRegister($selfRegisterData);
    }

    /** @test */
    public function createCoDeputy()
    {
        $invitedCoDeputy = UserHelpers::createInvitedCoDeputy();
        $createdCoDeputy = $invitedCoDeputy->setRegistrationDate(new DateTime());
        $invitedByDeputy = UserHelpers::createInvitedCoDeputy();

        $clientId = 100;

        $this->restClient
            ->post(sprintf('codeputy/add/%s', $clientId), $invitedCoDeputy, ['codeputy'], 'User')
            ->shouldBeCalled()
            ->willReturn($createdCoDeputy);

        $coDeputyCreatedEvent = new CoDeputyCreatedEvent($createdCoDeputy, $invitedByDeputy);
        $this->eventDispatcher->dispatch($coDeputyCreatedEvent, 'codeputy.created')->shouldBeCalled();

        $this->sut->createCoDeputy($invitedCoDeputy, $invitedByDeputy, $clientId);
    }

    /** @test */
    public function createOrgUser()
    {
        $userToCreate = UserHelpers::createUser();

        $this->restClient->post('user', $userToCreate, ['org_team_add'], 'User')->shouldBeCalled()->willReturn($userToCreate);

        $userCreatedEvent = new OrgUserCreatedEvent($userToCreate);
        $this->eventDispatcher->dispatch($userCreatedEvent, 'org.user.created')->shouldBeCalled();

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
        $this->restClient
            ->get(sprintf('user/get-primary-email/%d', $deputyUid), 'raw')
            ->shouldBeCalled()
            ->willReturn('{"data": null}');

        $result = $this->sut->returnPrimaryEmail($deputyUid);

        self::assertNull($result);
    }

    public function testReturnPrimaryEmail(): void
    {
        $expectedEmail = 'fakeemail@nowhere.biz.uk';

        // non-null deputy UID, deputy found
        $deputyUid = 77777777;
        $this->restClient
            ->get(sprintf('user/get-primary-email/%d', $deputyUid), 'raw')
            ->shouldBeCalled()
            ->willReturn("{\"data\": \"$expectedEmail\"}");

        $result = $this->sut->returnPrimaryEmail($deputyUid);

        self::assertEquals($expectedEmail, $result);
    }
}
