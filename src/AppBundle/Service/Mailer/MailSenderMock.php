<?php

namespace AppBundle\Service\Mailer;

use AppBundle\Model\Email;
use Predis\Client as PredisClient;
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
     * @var PredisClient
     */
    private $redis;

    /**
     * @var Symfony\Component\Validator\Validator
     */
    protected $validator;

    /**
     * @param ValidatorInterface $validator
     * @param \AppBundle\Mailer\MailerService $apiClient
     */
    public function __construct(ValidatorInterface $validator, PredisClient $redis)
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
        //validate change password object
        $errors = $this->validator->validate($email, null, $groups);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new \Exception($errorsString);
        }

        if (!isset($this->mailers[$transport])) {
            throw new \InvalidArgumentException("Email tranport $transport not found.");
        }

        $mailerService = $this->mailers[$transport];

        // convert Email->Swift_Message
        $swiftMessage = $mailerService->createMessage();
        /* @var $swiftMessage Swift_Message */
        $this->fillSwiftMessageWithEmailData($swiftMessage, $email);

        // read existing emails
        $emails = json_decode($this->getMockedEmailsRaw(), true) ?: [];

        // prepend email into the file
        $messageArray = MessageUtils::messageToArray($swiftMessage);

        array_unshift($emails, $messageArray);

        $this->redis->set(self::REDIS_EMAIL_KEY, json_encode($emails));

        return ['result' => true];
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
