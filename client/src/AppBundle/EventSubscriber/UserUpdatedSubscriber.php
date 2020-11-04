<?php declare(strict_types=1);


namespace AppBundle\EventSubscriber;

use AppBundle\Entity\User;
use AppBundle\Event\UserUpdatedEvent;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use AppBundle\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserUpdatedSubscriber implements EventSubscriberInterface
{
    /** @var DateTimeProvider */
    private $dateTimeProvider;

    /** @var LoggerInterface */
    private $logger;

    /** @var MailFactory */
    private $mailFactory;

    /** @var MailSender */
    private $mailSender;

    public function __construct(DateTimeProvider $dateTimeProvider, LoggerInterface $logger, MailFactory $mailFactory, MailSender $mailSender)
    {
        $this->dateTimeProvider = $dateTimeProvider;
        $this->logger = $logger;
        $this->mailFactory = $mailFactory;
        $this->mailSender = $mailSender;
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
        if ($event->getPostUpdateRoleName() === User::ROLE_LAY_DEPUTY && $this->userDetailsHaveChanged($event)) {
            $updateDeputyDetailsEmail = $this->mailFactory->createUpdateDeputyDetailsEmail($event->getPostUpdateUser());
            $this->mailSender->send($updateDeputyDetailsEmail);
        }
    }

    /**
     * @param UserUpdatedEvent $event
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
}
