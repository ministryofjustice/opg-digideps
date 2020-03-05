<?php

namespace AppBundle\Service\Mailer;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;
use AppBundle\Model\Email;
use Psr\Log\LoggerInterface;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MailSender implements MailSenderInterface
{
    /**
     * @var ValidatorInterface
     */
    protected $validator;

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
     *
     * @param ValidatorInterface $validator
     * @param LoggerInterface $logger
     * @param NotifyClient $notifyClient
     */
    public function __construct(
        ValidatorInterface $validator,
        LoggerInterface $logger,
        NotifyClient $notifyClient
    )
    {
        $this->validator = $validator;
        $this->logger = $logger;
        $this->notifyClient = $notifyClient;
    }

    /**
     * @param Email $email
     * @param array $groups
     *
     * @throws \Exception
     *
     * @return type
     *
     */
    public function send(Email $email, array $groups = ['text'], $transport = 'default')
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
