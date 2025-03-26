<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\Client;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Event\ClientDeletedEvent;
use App\Event\ClientUpdatedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
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
    private const CREATE_CLIENT = 'client/upsert';
    private const UNARCHIVE_CLIENT = 'client/%s/unarchive';
    private const GET_CLIENT_BY_ID_V2 = 'v2/client/%s';
    private const GET_CLIENT_BY_CASE_NUMBER_V2 = 'v2/client/case-number/%s';
    private const UPDATE_CLIENT_DEPUTY = 'client/%d/update-deputy/%d';
    private const GET_ALL_CLIENTS_BY_DEPUTY_UID = 'client/get-all-clients-by-deputy-uid/%s';

    private RestClientInterface $restClient;
    private RouterInterface $router;
    private LoggerInterface $logger;
    private UserApi $userApi;
    private DateTimeProvider $dateTimeProvider;
    private TokenStorageInterface $tokenStorage;
    private ObservableEventDispatcher $eventDispatcher;

    public function __construct(
        RestClientInterface $restClient,
        RouterInterface $router,
        LoggerInterface $logger,
        UserApi $userApi,
        DateTimeProvider $dateTimeProvider,
        TokenStorageInterface $tokenStorage,
        ObservableEventDispatcher $eventDispatcher,
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
     * Generates client profile link. We cannot guarantee the passed client has access to current report
     * So we need to make another API call with the correct JMS groups
     * thus ensuring the client is retrieved with the current report.
     *
     * @throws \Exception
     */
    public function generateClientProfileLink(Client $client): string
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
     * @param string[] $jmsGroups
     *
     * @return Client|null
     */
    public function getFirstClient($jmsGroups = ['user', 'user-clients', 'client'])
    {
        $user = $this->userApi->getUserWithData($jmsGroups);
        $clients = $user->getClients();

        return (is_array($clients) && !empty($clients[0]) && $clients[0] instanceof Client) ? $clients[0] : null;
    }

    public function getWithUsersV2(int $clientId): Client
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
                'client-deputy',
                'deputy',
                'client-organisations',
                'organisation',
            ]
        );
    }

    public function getById(int $clientId): Client
    {
        return $this->restClient->get(
            sprintf(self::GET_CLIENT_BY_ID, $clientId),
            'Client',
            [
                'client',
                'client-reports',
                'client-ndr',
                'ndr',
                'report',
                'status',
                'client-deputy',
                'deputy',
                'client-organisations',
                'organisation',
            ]
        );
    }

    public function delete(int $id, string $trigger): void
    {
        $clientWithUsers = $this->getWithUsersV2($id);
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $clientDeletedEvent = new ClientDeletedEvent($clientWithUsers, $currentUser, $trigger);

        $this->restClient->delete(sprintf(self::DELETE_CLIENT_BY_ID, $id));

        $this->eventDispatcher->dispatch($clientDeletedEvent, ClientDeletedEvent::NAME);
    }

    public function update(Client $preUpdateClient, Client $postUpdateClient, string $trigger)
    {
        $response = $this->restClient->put(self::UPDATE_CLIENT, $postUpdateClient, ['pa-edit', 'edit']);
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $clientUpdatedEvent = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $currentUser, $trigger);

        $this->eventDispatcher->dispatch($clientUpdatedEvent, ClientUpdatedEvent::NAME);

        return $response;
    }

    public function getByCaseNumber(string $caseNumber): Client
    {
        return $this->restClient->get(sprintf(self::GET_CLIENT_BY_CASE_NUMBER_V2, $caseNumber), 'Client');
    }

    public function unarchiveClient(string $id): void
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();
        $this->restClient->put(sprintf(self::UNARCHIVE_CLIENT, $id), $currentUser);
    }

    public function create(Client $client): bool
    {
        return $this->restClient->post(self::CREATE_CLIENT, $client);
    }

    public function updateDeputy(int $clientId, int $deputyId)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        return $this->restClient->put(sprintf(self::UPDATE_CLIENT_DEPUTY, $clientId, $deputyId), $currentUser);
    }

    /**
     * TODO can this be null?
     *
     * @return Client[]
     */
    public function getAllClientsByDeputyUid(int $deputyUid, $groups = [])
    {
        return $this->restClient->get(
            sprintf(self::GET_ALL_CLIENTS_BY_DEPUTY_UID, $deputyUid),
            'Client[]', $groups
        );
    }

    public function checkDeputyHasMultiClients(User $user): bool
    {
        // if we can't find the user's deputy UID, we can't look up their clients by UID using
        // getAllClientsByDeputyUid, so we don't know if they are a single or multi client deputy, and default to
        // single; this will disable the "choose a client" breadcrumb link
        $deputyUid = $user->getDeputyUid();
        if (is_null($deputyUid)) {
            $this->logger->error(
                "Deputy with ID {$user->getId()} has null deputy UID; ".
                'returning false from checkDeputyHasMultiClients() (unsure if they are a multi-client deputy)'
            );

            return false;
        }

        return 'ROLE_LAY_DEPUTY' == $user->getRoleName() && count($this->getAllClientsByDeputyUid($deputyUid)) > 1;
    }
}
