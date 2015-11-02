<?php

namespace AppBundle\Service\Mailer;

use Symfony\Component\Validator\ValidatorInterface;
use AppBundle\Model\Email;
use Swift_Mailer;
use Swift_Message;
use Swift_Mime_Message;
use Swift_Attachment;

class MailSender
{

    /**
     * @var ValidatorInterface 
     */
    protected $validator;

    /**
     * @var string 
     */
    protected $addressToMockRegexp;

    /**
     * @var string 
     */
    protected $mockPath;

    /**
     * @var Swift_Mailer[] 
     */
    private $mailers = [];


    /**
     * @param Validator $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->mailers = [];
        $this->validator = $validator;
    }


    /**
     * @param string $name
     * @param Swift_Mailer $mailer
     */
    public function addSwiftMailer($name, Swift_Mailer $mailer)
    {
        $this->mailers[$name] = $mailer;
    }


    /**
     * @param string $addressRegexpr
     * @param string $mockPath
     */
    public function writeToFileEmailMatching($addressRegexpr, $mockPath)
    {
        $this->addressToMockRegexp = $addressRegexpr;
        $this->mockPath = $mockPath;
    }


    /**
     * @param Email $email
     * @param array $groups
     * @return type
     * @throws \Exception
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

        $swiftMessage = $mailerService->createMessage(); /* @var $swiftMessage Swift_Message */
        $this->fillSwiftMessageWithEmailData($swiftMessage, $email);

        if ($this->isEmailToMock($swiftMessage)) {
            $result = $this->prependMessageIntoEmailMockPath($swiftMessage);
        } else {
            $result = $mailerService->send($swiftMessage);
        }

        return ['result' => $result];
    }
    
    /**
     * @param Swift_Message $swiftMessage
     * @param Email $email
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
     * @param Swift_Mime_Message $message
     * 
     * @return boolean true if 1st email adddress matches $this->addressToMockRegexp
     */
    private function isEmailToMock(Swift_Message $message)
    {
        if (!$this->addressToMockRegexp || !$this->mockPath) {
            return false;
        }

        // get "to"
        $to = $message->getTo();
        reset($to);
        $emailAddress = key($to);

        return preg_match($this->addressToMockRegexp, $emailAddress);
    }


    /**
     * //TODO move into fileWriter transport
     * @param Swift_Mime_Message $swiftMessage
     * 
     * @return string with debug info
     */
    private function prependMessageIntoEmailMockPath(Swift_Message $swiftMessage)
    {
        // read existing emails
        $emails = [];
        if (file_exists($this->mockPath)) {
            $emails = json_decode(file_get_contents($this->mockPath), true) ? : [];
        }
        
        // prepend email into the file
        array_unshift($emails, MessageUtils::messageToArray($swiftMessage));
        
        $ret = file_put_contents($this->mockPath, json_encode($emails));

        if (false === $ret) {
            throw new \RuntimeException("Cannot write email into {$this->mockPath}");
        }

        return "Email saved. $ret bytes written.";
    }

}