<?php

declare(strict_types=1);

namespace App\Service\Client\Internal;

use App\Entity\Ndr\Ndr;
use App\Entity\Report\Document;
use App\Event\NdrSubmittedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Exception\RestClientException;
use App\Service\Client\RestClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NdrApi
{
    private const SUBMIT_NDR_ENDPOINT = 'ndr/%s/submit?documentId=%s';
    private const GET_NDR_ENDPOINT = 'ndr/%s';

    /** @var RestClient */
    private $restClient;

    /** @var ObservableEventDispatcher */
    private $eventDispatcher;

    /** @var UserApi */
    private $userApi;

    public function __construct(RestClient $restClient, ObservableEventDispatcher $eventDispatcher, UserApi $userApi)
    {
        $this->restClient = $restClient;
        $this->eventDispatcher = $eventDispatcher;
        $this->userApi = $userApi;
    }

    public function submit(Ndr $ndrToSubmit, Document $ndrPdfDocument)
    {
        $this->restClient->put(
            sprintf(self::SUBMIT_NDR_ENDPOINT, $ndrToSubmit->getId(), $ndrPdfDocument->getId()),
            $ndrToSubmit,
            ['submit']
        );

        $submittedByWithClientsAndReports = $this->userApi->getUserWithData(['user-clients', 'client', 'client-reports', 'report']);
        $client = $submittedByWithClientsAndReports->getClients()[0];

        $ndrSubmittedEvent = new NdrSubmittedEvent($submittedByWithClientsAndReports, $ndrToSubmit, $client->getActiveReport());
        $this->eventDispatcher->dispatch($ndrSubmittedEvent, NdrSubmittedEvent::NAME);
    }

    public function getNdr(int $ndrId, array $groups = []): Ndr
    {
        $groups[] = 'ndr';

        $groups = array_unique($groups);
        sort($groups); // helps HTTP caching

        try {
            $ndr = $this->restClient->get(
                sprintf(self::GET_NDR_ENDPOINT, $ndrId),
                'Ndr\\Ndr',
                $groups
            );
        } catch (RestClientException $e) {
            if (403 === $e->getStatusCode() || 404 === $e->getStatusCode()) {
                throw new NotFoundHttpException($e->getData()['message']);
            } else {
                throw $e;
            }
        }

        return $ndr;
    }
}
