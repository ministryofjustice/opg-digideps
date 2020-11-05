<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Internal;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Event\ClientDeletedEvent;
use AppBundle\Event\ClientUpdatedEvent;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\RestClientInterface;
use AppBundle\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ClientApi
{
    public const CLIENT_ENDPOINT = 'client';

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

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        RestClientInterface $restClient,
        RouterInterface $router,
        LoggerInterface $logger,
        UserApi $userApi,
        DateTimeProvider $dateTimeProvider,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher
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
            sprintf('%s/%s', self::CLIENT_ENDPOINT, $client->getId()),
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
    public function getWithUsers(int $clientId)
    {
        return $this->restClient->get(
            sprintf('%s/%s/details', self::CLIENT_ENDPOINT, $clientId),
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
        $clientWithUsers = $this->getWithUsers($id);
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $clientDeletedEvent = new ClientDeletedEvent($clientWithUsers, $currentUser, $trigger);

        $this->restClient->delete(sprintf('%s/%s/delete', self::CLIENT_ENDPOINT, $id));

        $this->eventDispatcher->dispatch($clientDeletedEvent, ClientDeletedEvent::NAME);
    }

    /**
     * @param Client $preUpdateClient
     * @param Client $postUpdateClient
     * @param string $trigger
     */
    public function update(Client $preUpdateClient, Client $postUpdateClient, string $trigger)
    {
        $this->restClient->put(sprintf('%s/upsert', self::CLIENT_ENDPOINT), $postUpdateClient, ['pa-edit']);
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
        return $this->restClient->get(sprintf('v2/%s/case-number/%s', self::CLIENT_ENDPOINT, $caseNumber), 'Client');
    }
}
