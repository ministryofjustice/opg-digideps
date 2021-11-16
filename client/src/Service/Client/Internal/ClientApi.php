<?php

declare(strict_types=1);

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

    public function __construct(private RestClientInterface $restClient, private RouterInterface $router, private UserApi $userApi, private TokenStorageInterface $tokenStorage, private ObservableEventDispatcher $eventDispatcher)
    {
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

    /**
     * Generates client profile link. We cannot guarantee the passed client has access to current report
     * So we need to make another API call with the correct JMS groups
     * thus ensuring the client is retrieved with the current report.
     *
     * @return string
     *
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
        // generate link
        return $this->router->generate('report_overview', ['reportId' => $report->getId()]);
    }

    /**
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
                'organisation',
            ]
        );
    }

    /**
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
                'organisation',
            ]
        );
    }

    public function delete(int $id, string $trigger)
    {
        $clientWithUsers = $this->getWithUsersV2($id);
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $clientDeletedEvent = new ClientDeletedEvent($clientWithUsers, $currentUser, $trigger);

        $this->restClient->delete(sprintf(self::DELETE_CLIENT_BY_ID, $id));

        $this->eventDispatcher->dispatch($clientDeletedEvent, ClientDeletedEvent::NAME);
    }

    public function update(Client $preUpdateClient, Client $postUpdateClient, string $trigger)
    {
        $this->restClient->put(self::UPDATE_CLIENT, $postUpdateClient, ['pa-edit', 'edit']);
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $clientUpdatedEvent = new ClientUpdatedEvent($preUpdateClient, $postUpdateClient, $currentUser, $trigger);

        $this->eventDispatcher->dispatch($clientUpdatedEvent, ClientUpdatedEvent::NAME);
    }

    /**
     * @return Client
     */
    public function getByCaseNumber(string $caseNumber)
    {
        return $this->restClient->get(sprintf(self::GET_CLIENT_BY_CASE_NUMBER_V2, $caseNumber), 'Client');
    }
}
