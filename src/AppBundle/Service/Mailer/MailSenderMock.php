<?php

namespace AppBundle\Service\Mailer;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Validator\Validator;
use AppBundle\Model\Email;
use Symfony\Component\DependencyInjection\Container;
use Swift_Message;
use Swift_Attachment;

class MailSenderMock extends MailSender
{
    private static $messagesSent = [];

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;

    protected $validator;

    /**
     * @param \AppBundle\Mailer\MailerService $apiClient
     * @param Translator                      $translator
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->translator = $container->get('translator');
        $this->router = $container->get('router');
        $this->validator = $container->get('validator');
    }

    /**
     * @param Email $email
     * @param array $groups
     *
     * @return type
     *
     * @throws \Exception
     */
    public function send(Email $email, array $groups = ['text'], $transport = 'default')
    {
        //validate change password object
        $errors = $this->validator->validate($email, $groups);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new \Exception($errorsString);
        }
        $ret = $this->doSend($transport, $email);

        return $ret;
    }

    public function doSend($transport, Email $email)
    {
        $mailTransport = 'mailer.transport.smtp.default';
        if ($transport == 'secure-smtp') {
            $mailTransport = 'mailer.transport.smtp.secure';
        }

        // convert Email->Swift_Message
        //TODO move to helper/factory class
        $mailerService = new \Swift_Mailer($this->container->get($mailTransport));
        $message = $mailerService->createMessage(); /* @var $message Swift_Message */
        $message->setTo($email->getToEmail(), $email->getToName());
        $message->setFrom($email->getFromEmail(), $email->getFromName());

        $message->setSubject($email->getSubject());
        $message->setBody($email->getBodyText());
        $message->addPart($email->getBodyHtml(), 'text/html');

        foreach ($email->getAttachments() as $attachment) {
            $message->attach(new Swift_Attachment($attachment->getContent(), $attachment->getFilename(), $attachment->getContentType()));
        }

        self::$messagesSent[$mailTransport][] = MessageUtils::messageToArray($message);

        return ['result' => true];
    }

    /**
     * @return array
     */
    public static function getMessagesSent()
    {
        return self::$messagesSent;
    }

    public static function resetessagesSent()
    {
        self::$messagesSent = [];
    }
}
