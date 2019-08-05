<?php

namespace AppBundle\Service\Mailer;

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
     * @var Swift_Mailer[]
     */
    private $mailers = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MailSender constructor.
     *
     * @param ValidatorInterface $validator
     * @param LoggerInterface    $logger
     */
    public function __construct(ValidatorInterface $validator, LoggerInterface $logger)
    {
        $this->mailers = [];
        $this->validator = $validator;
        $this->logger = $logger;
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
        $errors = $this->validator->validate($email, null, $groups);
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

        $failedRecipients = [];
        $result = $mailerService->send($swiftMessage, $failedRecipients);

        // log email result
        $this->logger->log($result ? 'info' : 'error', 'Email sent: ', ['extra' => [
            'page' => 'mail_sender',
            'transport' => $transport,
            'to' => '***' . substr($to, 3),
            'result' => $result,
            'failedRecipients' => $failedRecipients ? implode(',', $failedRecipients) : '',
        ]]);


        return ['result' => $result];
    }

    /**
     * @param Swift_Message $swiftMessage
     * @param Email         $email
     */
    private function fillSwiftMessageWithEmailData(Swift_Message $swiftMessage, Email $email)
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
}
