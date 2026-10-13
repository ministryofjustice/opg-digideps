<?php

namespace OPG\Digideps\Frontend\Service\Mailer;

use Alphagov\Notifications\Client as NotifyClient;
use OPG\Digideps\Common\Mail\NotifyMail;
use OPG\Digideps\Common\Mail\MailSenderInterface;
use OPG\Digideps\Frontend\Service\Audit\AuditEvents;
use OPG\Digideps\Frontend\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class MailSender implements MailSenderInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private NotifyClient $notifyClient,
        private DateTimeProvider $dateTimeProvider,
        private TokenStorageInterface $tokenStorage
    ) {
    }

    public function send(NotifyMail $email): bool
    {
        try {
            $currentUser = $this->tokenStorage?->getToken()?->getUser();

            $this->notifyClient->sendEmail(
                $email->toEmail->value,
                $email->template->value,
                $email->parameters,
                '',
                $email->notifyFromId
            );

            $this->logger->notice(
                '',
                new AuditEvents($this->dateTimeProvider)->emailSent($email, $currentUser)
            );
        } catch (\Throwable $exception) {
            $this->logger->error(sprintf('Error sending email: %s', $exception->getMessage()));

            $currentUser = $this->tokenStorage?->getToken()?->getUser();

            $this->logger->notice(
                '',
                new AuditEvents($this->dateTimeProvider)->emailNotSent($email, $currentUser, $exception)
            );

            return false;
        }

        return true;
    }
}
