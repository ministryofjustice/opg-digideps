<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\EventSubscriber;

use OPG\Digideps\Frontend\Event\ClientUpdatedEvent;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Mailer\Mailer;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClientUpdatedSubscriber implements EventSubscriberInterface
{
    /** @var DateTimeProvider */
    private $dateTimeProvider;

    /** @var LoggerInterface */
    private $logger;

    /** @var Mailer */
    private $mailer;

    public function __construct(LoggerInterface $logger, DateTimeProvider $dateTimeProvider, Mailer $mailer)
    {
        $this->dateTimeProvider = $dateTimeProvider;
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            ClientUpdatedEvent::NAME => [
                ['logEvent', 2],
                ['sendEmail', 1],
            ],
        ];
    }

    public function logEvent(ClientUpdatedEvent $clientUpdatedEvent)
    {
        if ($this->emailHasChanged($clientUpdatedEvent)) {
            $event = (new AuditEvents($this->dateTimeProvider))->clientEmailChanged(
                $clientUpdatedEvent->getTrigger(),
                $clientUpdatedEvent->getPreUpdateClient()->getEmail(),
                $clientUpdatedEvent->getPostUpdateClient()->getEmail(),
                $clientUpdatedEvent->getChangedBy()->getEmail(),
                $clientUpdatedEvent->getPostUpdateClient()->getFullName(),
            );

            $message = empty($clientUpdatedEvent->getPostUpdateClient()->getEmail()) ? 'Client email address removed' : '';
            $this->logger->notice($message, $event);
        }
    }

    public function sendEmail(ClientUpdatedEvent $event)
    {
        if ($this->shouldSendEmail($event)) {
            $this->mailer->sendUpdateClientDetailsEmail($event->getPostUpdateClient());
        }
    }

    private function shouldSendEmail(ClientUpdatedEvent $event)
    {
        return $event->getChangedBy()->isLayDeputy() &&
            $this->clientDetailsHaveChanged($event) &&
            $this->clientsAreTheSame($event);
    }

    /**
     * @return bool
     */
    private function emailHasChanged(ClientUpdatedEvent $event)
    {
        return $event->getPreUpdateClient()->getEmail() !== $event->getPostUpdateClient()->getEmail();
    }

    private function clientDetailsHaveChanged(ClientUpdatedEvent $event)
    {
        // Purposeful using non-strict comparison here as we're just interested in testing properties being different
        // rather than the objects being strictly different
        return $event->getPostUpdateClient() != $event->getPreUpdateClient();
    }

    private function clientsAreTheSame(ClientUpdatedEvent $event)
    {
        return $event->getPreUpdateClient()->getId() === $event->getPostUpdateClient()->getId();
    }
}
