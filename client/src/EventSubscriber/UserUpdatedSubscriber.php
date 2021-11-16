<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\User;
use App\Event\UserUpdatedEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Mailer\Mailer;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserUpdatedSubscriber implements EventSubscriberInterface
{
    /** @var DateTimeProvider */
    private $dateTimeProvider;

    /** @var LoggerInterface */
    private $logger;

    /** @var Mailer */
    private $mailer;

    public function __construct(
        DateTimeProvider $dateTimeProvider,
        LoggerInterface $logger,
        Mailer $mailer
    ) {
        $this->dateTimeProvider = $dateTimeProvider;
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            UserUpdatedEvent::NAME => 'auditLog',
            UserUpdatedEvent::NAME => 'sendEmail',
        ];
    }

    public function auditLog(UserUpdatedEvent $event)
    {
        if ($this->emailHasChanged($event)) {
            $emailChangedEvent = (new AuditEvents($this->dateTimeProvider))
                ->userEmailChanged(
                    $event->getTrigger(),
                    $event->getPreUpdateUser()->getEmail(),
                    $event->getPostUpdateUser()->getEmail(),
                    $event->getCurrentUser()->getEmail(),
                    $event->getPostUpdateUser()->getFullName(),
                    $event->getPostUpdateUser()->getRoleName()
                );

            $this->logger->notice('', $emailChangedEvent);
        }

        if ($this->roleHasChanged($event)) {
            $roleChangedEvent = (new AuditEvents($this->dateTimeProvider))
                ->roleChanged(
                    $event->getTrigger(),
                    $event->getPreUpdateUser()->getRoleName(),
                    $event->getPostUpdateUser()->getRoleName(),
                    $event->getCurrentUser()->getEmail(),
                    $event->getPostUpdateUser()->getEmail()
                );

            $this->logger->notice('', $roleChangedEvent);
        }
    }

    public function sendEmail(UserUpdatedEvent $event)
    {
        if ($this->layDeputyDetailsChanged($event)) {
            $this->mailer->sendUpdateDeputyDetailsEmail($event->getPostUpdateUser());
        }
    }

    /**
     * @return bool
     */
    private function emailHasChanged(UserUpdatedEvent $event)
    {
        return $event->getPreUpdateUser()->getEmail() !== $event->getPostUpdateUser()->getEmail();
    }

    private function roleHasChanged(UserUpdatedEvent $event)
    {
        return $event->getPreUpdateUser()->getRoleName() !== $event->getPostUpdateUser()->getRoleName();
    }

    private function userDetailsHaveChanged(UserUpdatedEvent $event)
    {
        $pre = $event->getPreUpdateUser();
        $post = $event->getPostUpdateUser();

        return $pre->getFullName() !== $post->getFullName() ||
            $pre->getAddress1() !== $post->getAddress1() ||
            $pre->getAddress2() !== $post->getAddress2() ||
            $pre->getAddress3() !== $post->getAddress3() ||
            $pre->getAddressPostcode() !== $post->getAddressPostcode() ||
            $pre->getAddressCountry() !== $post->getAddressCountry() ||
            $pre->getPhoneMain() !== $post->getPhoneMain() ||
            $pre->getPhoneAlternative() !== $post->getPhoneAlternative() ||
            $this->emailHasChanged($event);
    }

    private function layDeputyDetailsChanged(UserUpdatedEvent $event)
    {
        return User::ROLE_LAY_DEPUTY === $event->getPostUpdateUser()->getRoleName() && $this->userDetailsHaveChanged($event);
    }
}
