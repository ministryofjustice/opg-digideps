<?php declare(strict_types=1);

namespace DigidepsTests\Service\Client\Internal;

use AppBundle\Event\ClientDeletedEvent;
use AppBundle\Event\ClientUpdatedEvent;
use AppBundle\Service\Client\Internal\ClientApi;
use AppBundle\Service\Client\Internal\UserApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Time\DateTimeProvider;
use AppBundle\TestHelpers\ClientHelpers;
use AppBundle\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ClientApiTest extends TestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $restClient;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $router;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $logger;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $userApi;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $dateTimeProvider;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $tokenStorage;

    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $eventDispatcher;

    /**
     * @var ClientApi
     */
    private $sut;

    public function setUp(): void
    {
        $this->restClient = self::prophesize(RestClient::class);
        $this->router = self::prophesize(RouterInterface::class);
        $this->logger = self::prophesize(LoggerInterface::class);
        $this->userApi = self::prophesize(UserApi::class);
        $this->dateTimeProvider = self::prophesize(DateTimeProvider::class);
        $this->tokenStorage = self::prophesize(TokenStorageInterface::class);
        $this->eventDispatcher = self::prophesize(EventDispatcher::class);

        $this->sut = new ClientApi(
            $this->restClient->reveal(),
            $this->router->reveal(),
            $this->logger->reveal(),
            $this->userApi->reveal(),
            $this->dateTimeProvider->reveal(),
            $this->tokenStorage->reveal(),
            $this->eventDispatcher->reveal()
        );
    }

    /** @test */
    public function delete()
    {
        $clientWithUsers = ClientHelpers::createClientWithUsers();
        $currentUser = UserHelpers::createUser();

        $this->restClient->get(sprintf('client/%s/details', $clientWithUsers->getId()), Argument::cetera())
            ->shouldBeCalled()
            ->willReturn($clientWithUsers);

        $usernamePasswordToken = new UsernamePasswordToken($currentUser, 'password', 'key');
        $this->tokenStorage->getToken()->willReturn($usernamePasswordToken);

        $this->restClient->delete(sprintf('client/%s/delete', $clientWithUsers->getId()))->shouldBeCalled();

        $trigger = 'A_TRIGGER';
        $clientDeletedEvent = new ClientDeletedEvent($clientWithUsers, $currentUser, $trigger);
        $this->eventDispatcher->dispatch('client.deleted', $clientDeletedEvent)->shouldBeCalled();

        $this->sut->delete($clientWithUsers->getId(), $trigger);
    }

    /** @test */
    public function update()
    {
        $preUpdateClient = ClientHelpers::createClient();
        $postUpdateClient = ClientHelpers::createClient();
        $currentUser = UserHelpers::createUser();
        $trigger = 'SOME_TRIGGER';

        $this->restClient->put('client/upsert', $postUpdateClient, Argument::cetera())->shouldBeCalled();

        $usernamePasswordToken = new UsernamePasswordToken($currentUser, 'password', 'key');
        $this->tokenStorage->getToken()->willReturn($usernamePasswordToken);

        $clientUpdatedEvent = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $currentUser, $trigger);

        $this->eventDispatcher->dispatch('client.updated', $clientUpdatedEvent)->shouldBeCalled();

        $this->sut->update($preUpdateClient, $postUpdateClient, $trigger);
    }
}
