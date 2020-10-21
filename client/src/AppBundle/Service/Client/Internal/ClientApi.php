<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Internal;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Service\Client\RestClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;

class ClientApi
{
    /** @var RestClient */
    private $restClient;

    /** @var RouterInterface */
    private $router;

    /** @var LoggerInterface */
    private $logger;

    /** @var UserApi */
    private $userApi;

    public function __construct(
        RestClient $restClient,
        RouterInterface $router,
        LoggerInterface $logger,
        UserApi $userApi
    ) {
        $this->restClient = $restClient;
        $this->router = $router;
        $this->logger = $logger;
        $this->userApi = $userApi;
    }

    /**
     * @return Client|null
     */
    public function getFirstClient($groups = ['user', 'user-clients', 'client'])
    {
        $user = $this->userApi->getUserWithData($groups);
        $clients = $user->getClients();

        return (is_array($clients) && !empty($clients[0]) && $clients[0] instanceof Client) ? $clients[0] : null;
    }

    /**
     * Generates client profile link. We cannot guarantee the passed client has access to current report
     * So we need to make another API call with the correct JMS groups
     * thus ensuring the client is retrieved with the current report.
     *
     * @param  Client     $client
     * @throws \Exception
     * @return string
     */
    public function generateClientProfileLink(Client $client)
    {
        /** @var Client $client */
        $client = $this->restClient->get('client/' . $client->getId(), 'Client', ['client', 'report-id', 'current-report']);

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

}
