<?php declare(strict_types=1);


namespace AppBundle\Service\Client\Internal;

use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Event\NdrSubmittedEvent;
use AppBundle\Service\Client\RestClient;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NdrApi
{
    private const SUBMIT_NDR_ENDPOINT = 'ndr/%s/submit?documentId=%s';

    /** @var RestClient */
    private $restClient;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(RestClient $restClient, EventDispatcherInterface $eventDispatcher)
    {
        $this->restClient = $restClient;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function submit(Ndr $ndrToSubmit, Document $ndrPdfDocument, User $submittedBy, Client $client)
    {
        $this->restClient->put(
            sprintf(self::SUBMIT_NDR_ENDPOINT, $ndrToSubmit->getId(), $ndrPdfDocument->getId()),
            $ndrToSubmit,
            ['submit']
        );

        $ndrSubmittedEvent = new NdrSubmittedEvent($submittedBy, $ndrToSubmit, $client->getActiveReport());
        $this->eventDispatcher->dispatch(NdrSubmittedEvent::NAME, $ndrSubmittedEvent);
    }
}
