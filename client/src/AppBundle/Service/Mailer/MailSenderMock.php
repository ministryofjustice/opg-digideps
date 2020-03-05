<?php declare(strict_types=1);

namespace AppBundle\Service\Mailer;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;
use AppBundle\Model\Email;
use Psr\Log\LoggerInterface;

class MailSenderMock
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
     * @param LoggerInterface $logger
     * @param NotifyClient $notifyClient
     */
    public function __construct(LoggerInterface $logger, NotifyClient $notifyClient)
    {
        $this->logger = $logger;
        $this->notifyClient = $notifyClient;
    }

    /**
     * @param Email $email
     * @return array|bool
     */
    public function send(Email $email)
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
            throw $exception;
        }

        return true;
    }
}
