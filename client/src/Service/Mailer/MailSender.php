<?php

namespace App\Service\Mailer;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;
use App\Model\Email;
use Psr\Log\LoggerInterface;

class MailSender implements MailSenderInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var NotifyClient
     */
    private $notifyClient;

    /**
     * MailSender constructor.
     */
    public function __construct(
        LoggerInterface $logger,
        NotifyClient $notifyClient
    ) {
        $this->logger = $logger;
        $this->notifyClient = $notifyClient;
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
