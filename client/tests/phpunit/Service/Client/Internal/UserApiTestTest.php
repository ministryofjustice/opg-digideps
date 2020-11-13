<?php declare(strict_types=1);


use AppBundle\Event\UserDeletedEvent;
use AppBundle\Event\UserUpdatedEvent;
use AppBundle\Service\Client\Internal\UserApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserApiTest extends TestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $restClient;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $tokenStorage;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $eventDispatcher;

    /**
     * @var UserApi
     */
    private $sut;

    public function setUp(): void
    {
        $this->restClient = self::prophesize(RestClient::class);
        $this->tokenStorage = self::prophesize(TokenStorageInterface::class);
        $this->eventDispatcher = self::prophesize(EventDispatcher::class);

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
}
