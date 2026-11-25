<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Mail;

use Alphagov\Notifications\Client;
use OPG\Digideps\Backend\Service\Audit\AuditEvents;
use OPG\Digideps\Backend\Service\Time\DateTimeProvider;
use OPG\Digideps\Common\Mail\MailSenderInterface;
use OPG\Digideps\Common\Mail\NotifyMail;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class MailSender implements MailSenderInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private Client $notifyClient,
        private TokenStorageInterface $tokenStorage,
        private DateTimeProvider $dateTimeProvider
    ) {
    }

    public function send(NotifyMail $email): bool
    {
        try {
            $currentUser = $this->tokenStorage->getToken()?->getUser();

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

            $currentUser = $this->tokenStorage->getToken()?->getUser();

            $this->logger->notice(
                '',
                new AuditEvents($this->dateTimeProvider)->emailNotSent($email, $currentUser, $exception)
            );

            return false;
        }

        return true;
    }
}
