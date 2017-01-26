<?php

namespace AppBundle\Service\Mailer;

use AppBundle\Model\Email;
use Predis\Client as PredisClient;
use Psr\Log\LoggerInterface;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Swift_Mime_Message;
use Symfony\Component\Validator\ValidatorInterface;

class MailSender
{
    /**
     * Emails with "to" matching this expression will be written into redis
     * rather than using the real SMTP service
     */
    const MOCK_EMAILS_REGEXPR = '/^behat-/i';

    /**
     * REDIS key used to store email mocks
     */
    const REDIS_EMAIL_KEY = 'behatEmailMock';

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var Swift_Mailer[]
     */
    private $mailers = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PredisClient
     */
    private $redis;

    /**
     * MailSender constructor.
     *
     * @param ValidatorInterface $validator
     * @param LoggerInterface    $logger
     * @param PredisClient       $redis
     */
    public function __construct(ValidatorInterface $validator, LoggerInterface $logger, PredisClient $redis)
    {
        $this->mailers = [];
        $this->validator = $validator;
        $this->logger = $logger;
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
        $errors = $this->validator->validate($email, $groups);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new \RuntimeException($errorsString);
        }

        if (!isset($this->mailers[$transport])) {
            throw new \InvalidArgumentException("Email tranport $transport not found.");
        }
        $mailerService = $this->mailers[$transport];

        $swiftMessage = $mailerService->createMessage();
        /* @var $swiftMessage Swift_Message */
        $this->fillSwiftMessageWithEmailData($swiftMessage, $email);

        $to = $this->getFirstTo($swiftMessage);

        // write into redis only, if needed
        if (preg_match(self::MOCK_EMAILS_REGEXPR, $to)) {
            $result = $this->prependMessageIntoEmailMock($swiftMessage);

            return ['result' => $result];
        }
        $failedRecipients = [];
        $result = $mailerService->send($swiftMessage, $failedRecipients);

        // log email result
        $this->logger->log($result ? 'info' : 'error', 'Email sent: ', ['extra' => [
            'page' => 'mail_sender',
            'transport' => $transport,
            'to' => '***'.substr($to, 3),
            'result' => $result,
            'failedRecipients' => $failedRecipients ? implode(',', $failedRecipients) : '',
        ]]);


        return ['result' => $result];
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
     * Get first address.
     *
     * @param Swift_Message $message
     *
     * @return string email
     */
    private function getFirstTo(Swift_Message $message)
    {
        $to = $message->getTo();
        reset($to);

        return key($to);
    }

    /**
     * @param Swift_Mime_Message $swiftMessage
     *
     * @return string with debug info
     */
    private function prependMessageIntoEmailMock(Swift_Message $swiftMessage)
    {
        // read existing emails
        $emails = json_decode($this->getMockedEmailsRaw(), true) ?: [];

        // prepend email into the file
        $messageArray = MessageUtils::messageToArray($swiftMessage);

        array_unshift($emails, $messageArray);

        return $this->redis->set(self::REDIS_EMAIL_KEY, json_encode($emails));
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
