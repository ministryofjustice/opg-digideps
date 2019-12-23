<?php

namespace AppBundle\Service\Mailer;

use Alphagov\Notifications\Exception\NotifyException;
use AppBundle\Model\Email;
use Predis\ClientInterface as PredisClientInterface;
use Psr\Log\LoggerInterface;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MailSenderMock implements MailSenderInterface
{
    /**
     * REDIS key used to store email mocks
     */
    const REDIS_EMAIL_KEY = 'behatEmailMock';

    /**
     * @var Swift_Mailer[]
     */
    private $mailers = [];

    /**
     * @var PredisClientInterface
     */
    private $redis;

    /**
     * @var Symfony\Component\Validator\Validator
     */
    protected $validator;


    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ValidatorInterface $validator
     * @param PredisClientInterface $redis
     */
    public function __construct(ValidatorInterface $validator, PredisClientInterface $redis, LoggerInterface $logger)
    {
        $this->mailers = [];
        $this->validator = $validator;
        $this->redis = $redis;
    }

    /**
     * @param string       $name
     * @param Swift_Mailer $mailer
     */
    public function addSwiftMailer($name, Swift_Mailer $mailer)
    {
        $this->mailers[$name] = $mailer;
    }

    /**
     * @param Swift_Message $swiftMessage
     * @param Email         $email
     */
    private function fillSwiftMessageWithEmailData(\Swift_Message $swiftMessage, Email $email)
    {
        $swiftMessage->setTo($email->getToEmail(), $email->getToName())
            ->setFrom($email->getFromEmail(), $email->getFromName())
            ->setSubject($email->getSubject())
            ->setBody($email->getBodyText());

        $swiftMessage->addPart($email->getBodyHtml(), 'text/html');

        foreach ($email->getAttachments() as $attachment) {
            $swiftMessage->attach(new Swift_Attachment($attachment->getContent(), $attachment->getFilename(), $attachment->getContentType()));
        }
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
        $notifyClient = new \Alphagov\Notifications\Client([
            'apiKey' => getenv('NOTIFY_API_KEY'),
            'httpClient' => new \Http\Adapter\Guzzle6\Client
        ]);

        try {
            $notifyClient->sendEmail(
                $email->getToEmail(),
                $email->getTemplate(),
                $email->getParameters()
            );
        } catch (NotifyException $exception) {
            $this->logger->error($exception->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @return string JSON string with all the emails
     */
    public function getMockedEmailsRaw()
    {
        return $this->redis->get(self::REDIS_EMAIL_KEY);
    }

    /**
     * reset mail mock (redis key)
     *
     * @return mixed
     */
    public function resetMockedEmails()
    {
        return $this->redis->set(self::REDIS_EMAIL_KEY, '');
    }
}
