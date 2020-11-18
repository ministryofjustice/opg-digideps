<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Internal;

use AppBundle\Event\GeneralFeedbackSubmittedEvent;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\RestClientInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SatisfactionApi
{
    private const CREATE_PUBLIC_ENDPOINT = 'satisfaction/public';

    /** @var RestClient */
    private $restClient;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(RestClientInterface $restClient, EventDispatcherInterface $eventDispatcher)
    {
        $this->restClient = $restClient;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param array $formResponse
     */
    public function create(array $formResponse): void
    {
        $this->restClient->post(
            self::CREATE_PUBLIC_ENDPOINT,
            ['score' => $formResponse['satisfactionLevel'], 'comments' => $formResponse['comments']]
        );

        $event = (new GeneralFeedbackSubmittedEvent())->setFeedbackFormResponse($formResponse);
        $this->eventDispatcher->dispatch(GeneralFeedbackSubmittedEvent::NAME, $event);
    }
}
