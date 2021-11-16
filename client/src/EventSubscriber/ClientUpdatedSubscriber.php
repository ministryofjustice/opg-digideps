<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\ClientUpdatedEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Mailer\Mailer;
use App\Service\Time\DateTimeProvider;
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
            ClientUpdatedEvent::NAME => 'logEvent',
            ClientUpdatedEvent::NAME => 'sendEmail',
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
