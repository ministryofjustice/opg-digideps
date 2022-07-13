<?php

namespace App\Service\Mailer;

use Alphagov\Notifications\Client as NotifyClient;
use App\Model\Email;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Throwable;

class MailSender implements MailSenderInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private NotifyClient $notifyClient,
        private DateTimeProvider $dateTimeProvider,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    public function send(Email $email): bool
    {
        try {
            $currentUser = $this->tokenStorage?->getToken()?->getUser();

            $this->notifyClient->sendEmail(
                $email->getToEmail(),
                $email->getTemplate(),
                $email->getParameters(),
                '',
                $email->getFromEmailNotifyID()
            );

            $this->logger->notice(
                '',
                (new AuditEvents($this->dateTimeProvider))->emailSent($email, $currentUser)
            );
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            $currentUser = $this->tokenStorage?->getToken()?->getUser();

            $this->logger->notice(
                '',
                (new AuditEvents($this->dateTimeProvider))->emailNotSent($email, $currentUser, $exception)
            );

            return false;
        }

        return true;
    }
}
