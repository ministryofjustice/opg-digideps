<?php declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Event\ClientDeletedEvent;
use App\Event\ClientUpdatedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Service\Client\RestClient;
use App\Service\Client\RestClientInterface;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ClientApi
{
    private const GET_CLIENT_BY_ID = 'client/%s';
    private const DELETE_CLIENT_BY_ID = 'client/%s/delete';
    private const UPDATE_CLIENT = 'client/upsert';

    private const GET_CLIENT_BY_ID_V2 = 'v2/client/%s';
    private const GET_CLIENT_BY_CASE_NUMBER_V2 = 'v2/client/case-number/%s';

    /** @var RestClient */
    private $restClient;

    /** @var RouterInterface */
    private $router;

    /** @var LoggerInterface */
    private $logger;

    /** @var UserApi */
    private $userApi;

    /** @var DateTimeProvider */
    private $dateTimeProvider;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ObservableEventDispatcher */
    private $eventDispatcher;

    public function __construct(
        RestClientInterface $restClient,
        RouterInterface $router,
        LoggerInterface $logger,
        UserApi $userApi,
        DateTimeProvider $dateTimeProvider,
        TokenStorageInterface $tokenStorage,
        ObservableEventDispatcher $eventDispatcher
    ) {
        $this->restClient = $restClient;
        $this->router = $router;
        $this->logger = $logger;
        $this->userApi = $userApi;
        $this->dateTimeProvider = $dateTimeProvider;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string[] $jmsGroups
     * @return Client|null
     */
    public function getFirstClient($jmsGroups = ['user', 'user-clients', 'client'])
    {
        $user = $this->userApi->getUserWithData($jmsGroups);
        $clients = $user->getClients();

        return (is_array($clients) && !empty($clients[0]) && $clients[0] instanceof Client) ? $clients[0] : null;
    }

    /**
     * Generates client profile link. We cannot guarantee the passed client has access to current report
     * So we need to make another API call with the correct JMS groups
     * thus ensuring the client is retrieved with the current report.
     *
     * @param Client $client
     * @return string
     * @throws \Exception
     */
    public function generateClientProfileLink(Client $client)
    {
        /** @var Client $client */
        $client = $this->restClient->get(
            sprintf(self::GET_CLIENT_BY_ID, $client->getId()),
            'Client',
            ['client', 'report-id', 'current-report']
        );

        $report = $client->getCurrentReport();

        if ($report instanceof Report) {
            // generate link
            return $this->router->generate('report_overview', ['reportId' => $report->getId()]);
        }

        $this->logger->log(
            'warning',
            'Client entity missing current report when trying to generate client profile link'
        );

        throw new \Exception('Unable to generate client profile link.');
    }

    /**
     * @param int $clientId
     * @return Client
     */
    public function getWithUsers(int $clientId, array $includes = [])
    {
        return $this->restClient->get(
            sprintf(self::GET_CLIENT_BY_ID, $clientId),
            'Client',
            [
                'client',
                'client-users',
                'user',
                'client-reports',
                'client-ndr',
                'ndr',
                'report',
                'status',
                'client-named-deputy',
                'named-deputy',
                'client-organisations',
                'organisation'
            ]
        );
    }

    /**
     * @param int $clientId
     * @return Client
     */
    public function getWithUsersV2(int $clientId, array $includes = [])
    {
        return $this->restClient->get(
            sprintf(self::GET_CLIENT_BY_ID_V2, $clientId),
            'Client',
            [
                'client',
                'client-users',
                'user',
                'client-reports',
                'client-ndr',
                'ndr',
                'report',
                'status',
                'client-named-deputy',
                'named-deputy',
                'client-organisations',
                'organisation'
            ]
        );
    }

    /**
     * @param int $id
     * @param string $trigger
     */
    public function delete(int $id, string $trigger)
    {
        $clientWithUsers = $this->getWithUsersV2($id);
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $clientDeletedEvent = new ClientDeletedEvent($clientWithUsers, $currentUser, $trigger);

        $this->restClient->delete(sprintf(self::DELETE_CLIENT_BY_ID, $id));

        $this->eventDispatcher->dispatch($clientDeletedEvent, ClientDeletedEvent::NAME);
    }

    /**
     * @param Client $preUpdateClient
     * @param Client $postUpdateClient
     * @param string $trigger
     */
    public function update(Client $preUpdateClient, Client $postUpdateClient, string $trigger)
    {
        $this->restClient->put(self::UPDATE_CLIENT, $postUpdateClient, ['pa-edit', 'edit']);
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $clientUpdatedEvent = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $currentUser, $trigger);

        $this->eventDispatcher->dispatch($clientUpdatedEvent, ClientUpdatedEvent::NAME);
    }

    /**
     * @param string $caseNumber
     * @return Client
     */
    public function getByCaseNumber(string $caseNumber)
    {
        return $this->restClient->get(sprintf(self::GET_CLIENT_BY_CASE_NUMBER_V2, $caseNumber), 'Client');
    }
}
