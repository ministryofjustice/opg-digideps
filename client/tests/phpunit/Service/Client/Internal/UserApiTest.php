<?php declare(strict_types=1);


use AppBundle\Event\CoDeputyCreatedEvent;
use AppBundle\Event\CoDeputyInvitedEvent;
use AppBundle\Event\DeputyInvitedEvent;
use AppBundle\Event\DeputySelfRegisteredEvent;
use AppBundle\Event\OrgUserCreatedEvent;
use AppBundle\Event\UserPasswordResetEvent;
use AppBundle\Event\AdminUserCreatedEvent;
use AppBundle\Event\UserDeletedEvent;
use AppBundle\Event\UserUpdatedEvent;
use AppBundle\EventDispatcher\EventDispatcherMock;
use AppBundle\EventDispatcher\ObservableEventDispatcher;
use AppBundle\Model\SelfRegisterData;
use AppBundle\Service\Client\Internal\UserApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\TestHelpers\UserHelpers;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserApiTest extends TestCase
{
    /** @var ObjectProphecy */
    private $restClient;

    /** @var ObjectProphecy */
    private $tokenStorage;

    /** @var ObjectProphecy */
    private $eventDispatcher;

    /** @var UserApi */
    private $sut;

    /** @var \Faker\Generator */
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

        $usernamePasswordToken = new UsernamePasswordToken($currentUser, 'password', 'key');
        $this->tokenStorage->getToken()->willReturn($usernamePasswordToken);

        $userUpdatedEvent = new UserUpdatedEvent($preUpdateUser, $postUpdateUser, $currentUser, $trigger);
        $this->eventDispatcher->dispatch('user.updated', $userUpdatedEvent)->shouldBeCalled();

        $this->sut->update($preUpdateUser, $postUpdateUser, $trigger, $jmsGroups);
    }

    /** @test */
    public function delete()
    {
        $userToDelete = UserHelpers::createUser();
        $deletedBy = UserHelpers::createUser();
        $trigger = 'SOME_TRIGGER';

        $this->restClient->delete(sprintf('user/%s', $userToDelete->getId()))->shouldBeCalled();

        $usernamePasswordToken = new UsernamePasswordToken($deletedBy, 'password', 'key');
        $this->tokenStorage->getToken()->willReturn($usernamePasswordToken);

        $userUpdatedEvent = new UserDeletedEvent($userToDelete, $deletedBy, $trigger);
        $this->eventDispatcher->dispatch('user.deleted', $userUpdatedEvent)->shouldBeCalled();

        $this->sut->delete($userToDelete, $trigger);
    }

    /** @test */
    public function createAdminUser()
    {
        $userToCreate = UserHelpers::createUser();

        $this->restClient->post('user', $userToCreate, ["admin_add_user"], 'User')->shouldBeCalled()->willReturn($userToCreate);

        $userCreatedEvent = new AdminUserCreatedEvent($userToCreate);
        $this->eventDispatcher->dispatch('admin.user.created', $userCreatedEvent)->shouldBeCalled();

        $this->sut->createAdminUser($userToCreate);
    }

    /** @test */
    public function resetPassword()
    {
        $userToResetPassword = UserHelpers::createUser();
        $email = $this->faker->safeEmail;

        $this->restClient
            ->apiCall('put', sprintf('user/recreate-token/%s', $email), null, 'User', [], false)
            ->shouldBeCalled()
            ->willReturn($userToResetPassword);

        $passwordResetEvent = new UserPasswordResetEvent($userToResetPassword);
        $this->eventDispatcher->dispatch('password.reset', $passwordResetEvent)->shouldBeCalled();

        $this->sut->resetPassword($email);
    }

    /** @test */
    public function reInviteCoDeputy()
    {
        $invitedCoDeputy = UserHelpers::createUser();
        $inviterDeputy = UserHelpers::createUser();
        $email = $this->faker->safeEmail;

        $this->restClient
            ->apiCall('put', sprintf('user/recreate-token/%s', $email), null, 'User', [], false)
            ->shouldBeCalled()
            ->willReturn($invitedCoDeputy);

        $coDeputyInvitedEvent = new CoDeputyInvitedEvent($invitedCoDeputy, $inviterDeputy);
        $this->eventDispatcher->dispatch('codeputy.invited', $coDeputyInvitedEvent)->shouldBeCalled();

        $this->sut->reInviteCoDeputy($email, $inviterDeputy);
    }

    /** @test */
    public function getByEmail()
    {
        $existingUser = UserHelpers::createUser();

        $this->restClient
            ->get(sprintf('user/get-one-by/email/%s', $existingUser->getEmail()), 'User')
            ->shouldBeCalled()
            ->willReturn($existingUser);

        $returnedUser = $this->sut->getByEmail($existingUser->getEmail());

        self::assertEquals($existingUser, $returnedUser);
    }

    /** @test */
    public function reInviteDeputy()
    {
        $invitedDeputy = UserHelpers::createUser();
        $email = $this->faker->safeEmail;

        $this->restClient
            ->apiCall('put', sprintf('user/recreate-token/%s', $email), null, 'User', [], false)
            ->shouldBeCalled()
            ->willReturn($invitedDeputy);

        $deputyInvitedEvent = new DeputyInvitedEvent($invitedDeputy);
        $this->eventDispatcher->dispatch('deputy.invited', $deputyInvitedEvent)->shouldBeCalled();

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
        $this->eventDispatcher->dispatch('deputy.self.registered', $deputySelfRegisteredEvent)->shouldBeCalled();

        $this->sut->selfRegister($selfRegisterData);
    }

    /** @test */
    public function createCoDeputy()
    {
        $invitedCoDeputy = UserHelpers::createInvitedCoDeputy();
        $createdCoDeputy = $invitedCoDeputy->setRegistrationDate(new DateTime());
        $invitedByDeputy = UserHelpers::createInvitedCoDeputy();

        $this->restClient
            ->post('codeputy/add', $invitedCoDeputy, ['codeputy'], 'User')
            ->shouldBeCalled()
            ->willReturn($createdCoDeputy);

        $coDeputyCreatedEvent = new CoDeputyCreatedEvent($createdCoDeputy, $invitedByDeputy);
        $this->eventDispatcher->dispatch('codeputy.created', $coDeputyCreatedEvent)->shouldBeCalled();

        $this->sut->createCoDeputy($invitedCoDeputy, $invitedByDeputy);
    }

    /** @test */
    public function createOrgUser()
    {
        $userToCreate = UserHelpers::createUser();

        $this->restClient->post('user', $userToCreate, ["org_team_add"], 'User')->shouldBeCalled()->willReturn($userToCreate);

        $userCreatedEvent = new OrgUserCreatedEvent($userToCreate);
        $this->eventDispatcher->dispatch('org.user.created', $userCreatedEvent)->shouldBeCalled();

        $this->sut->createOrgUser($userToCreate);
    }
}
