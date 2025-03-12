<?php

declare(strict_types=1);

namespace DigidepsTests\Service\Client\Internal;

use App\Event\ClientDeletedEvent;
use App\Event\ClientUpdatedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\Time\DateTimeProvider;
use App\TestHelpers\ClientHelpers;
use App\TestHelpers\UserHelpers;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ClientApiTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy
     */
    private $restClient;

    /**
     * @var ObjectProphecy
     */
    private $router;

    /**
     * @var ObjectProphecy
     */
    private $logger;

    /**
     * @var ObjectProphecy
     */
    private $userApi;

    /**
     * @var ObjectProphecy
     */
    private $dateTimeProvider;

    /**
     * @var ObjectProphecy
     */
    private $tokenStorage;

    /**
     * @var ObjectProphecy
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
        $this->eventDispatcher = self::prophesize(ObservableEventDispatcher::class);

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

        $this->restClient->get(sprintf('v2/client/%s', $clientWithUsers->getId()), Argument::cetera())
            ->shouldBeCalled()
            ->willReturn($clientWithUsers);

        $usernamePasswordToken = new UsernamePasswordToken($currentUser, 'firewall', $currentUser->getRoles());
        $this->tokenStorage->getToken()->willReturn($usernamePasswordToken);

        $this->restClient->delete(sprintf('client/%s/delete', $clientWithUsers->getId()))->shouldBeCalled();

        $trigger = 'A_TRIGGER';
        $clientDeletedEvent = new ClientDeletedEvent($clientWithUsers, $currentUser, $trigger);
        $this->eventDispatcher->dispatch($clientDeletedEvent, 'client.deleted')->shouldBeCalled();

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

        $usernamePasswordToken = new UsernamePasswordToken($currentUser, 'firewall', $currentUser->getRoles());
        $this->tokenStorage->getToken()->willReturn($usernamePasswordToken);

        $clientUpdatedEvent = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $currentUser, $trigger);

        $this->eventDispatcher->dispatch($clientUpdatedEvent, 'client.updated')->shouldBeCalled();

        $this->sut->update($preUpdateClient, $postUpdateClient, $trigger);
    }
}
