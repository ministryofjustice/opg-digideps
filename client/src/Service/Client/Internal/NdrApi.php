<?php declare(strict_types=1);


namespace App\Service\Client\Internal;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Document;
use App\Entity\User;
use App\Event\NdrSubmittedEvent;
use App\EventDispatcher\ObservableEventDispatcher;
use App\Service\Client\RestClient;

class NdrApi
{
    private const SUBMIT_NDR_ENDPOINT = 'ndr/%s/submit?documentId=%s';

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

        // Debug here and see what we have with the User -> client -> report getting mailfactory 419 error on cloning non-object

        $submittedByWithClientsAndReports = $this->userApi->getUserWithData(['user-clients', 'client', 'client-reports', 'report']);
        $client = $submittedByWithClientsAndReports->getClients()[0];

        $ndrSubmittedEvent = new NdrSubmittedEvent($submittedByWithClientsAndReports, $ndrToSubmit, $client->getActiveReport());
        $this->eventDispatcher->dispatch($ndrSubmittedEvent, NdrSubmittedEvent::NAME);
    }
}
