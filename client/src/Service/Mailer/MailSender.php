<?php

namespace App\Service\Mailer;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;
use App\Model\Email;
use Psr\Log\LoggerInterface;

class MailSender implements MailSenderInterface
{
    /**
     * MailSender constructor.
     */
    public function __construct(private LoggerInterface $logger, private NotifyClient $notifyClient)
    {
    }

    public function send(Email $email): bool
    {
        try {
            $this->notifyClient->sendEmail(
                $email->getToEmail(),
                $email->getTemplate(),
                $email->getParameters(),
                '',
                $email->getFromEmailNotifyID()
            );
        } catch (NotifyException $exception) {
            $this->logger->error($exception->getMessage());
            return false;
        }

        return true;
    }
}
