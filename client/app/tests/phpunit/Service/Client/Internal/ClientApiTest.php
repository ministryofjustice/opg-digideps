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
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ClientApiTest extends TestCase
{
    private RestClient $restClient;
    private RouterInterface $router;
    private LoggerInterface $logger;
    private UserApi $userApi;
    private DateTimeProvider $dateTimeProvider;
    private TokenStorageInterface $tokenStorage;
    private ObservableEventDispatcher $eventDispatcher;

    private ClientApi $sut;

    public function setUp(): void
    {
        $this->restClient = $this->createMock(RestClient::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userApi = $this->createMock(UserApi::class);
        $this->dateTimeProvider = $this->createMock(DateTimeProvider::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
        $this->eventDispatcher = $this->createMock(ObservableEventDispatcher::class);

        $this->sut = new ClientApi(
            $this->restClient,
            $this->router,
            $this->logger,
            $this->userApi,
            $this->dateTimeProvider,
            $this->tokenStorage,
            $this->eventDispatcher
        );
    }

    public function testDelete()
    {
        $clientWithUsers = ClientHelpers::createClientWithUsers();
        $currentUser = UserHelpers::createUser();

        $this->restClient->expects(static::once())
            ->method('get')
            ->with(sprintf('v2/client/%s', $clientWithUsers->getId()), 'Client', self::anything(), self::anything())
            ->willReturn($clientWithUsers);

        $usernamePasswordToken = new UsernamePasswordToken($currentUser, 'firewall', $currentUser->getRoles());
        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn($usernamePasswordToken);

        $this->restClient->expects(static::once())
            ->method('delete')
            ->with(sprintf('client/%s/delete', $clientWithUsers->getId()));

        $trigger = 'A_TRIGGER';
        $clientDeletedEvent = new ClientDeletedEvent($clientWithUsers, $currentUser, $trigger);
        $this->eventDispatcher->expects(static::once())
            ->method('dispatch')
            ->with($clientDeletedEvent, 'client.deleted');

        $this->sut->delete($clientWithUsers->getId(), $trigger);
    }

    public function testUpdate()
    {
        $preUpdateClient = ClientHelpers::createClient();
        $postUpdateClient = ClientHelpers::createClient();
        $currentUser = UserHelpers::createUser();
        $trigger = 'SOME_TRIGGER';

        /** @var ResponseInterface $mockResponse */
        $mockResponse = $this->createMock(ResponseInterface::class);
        $this->restClient->expects(static::once())
            ->method('put')
            ->with('client/upsert', $postUpdateClient, static::anything())
            ->willReturn($mockResponse);

        $usernamePasswordToken = new UsernamePasswordToken($currentUser, 'firewall', $currentUser->getRoles());
        $this->tokenStorage->expects(static::once())
            ->method('getToken')
            ->willReturn($usernamePasswordToken);

        $clientUpdatedEvent = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $currentUser, $trigger);

        $this->eventDispatcher->expects(static::once())
            ->method('dispatch')
            ->with($clientUpdatedEvent, 'client.updated');

        $this->sut->update($preUpdateClient, $postUpdateClient, $trigger);
    }
}
