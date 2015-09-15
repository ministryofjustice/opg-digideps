<?php
namespace AppBundle\Service\Mailer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Validator\Validator;
use AppBundle\Model\Email;
use Symfony\Component\DependencyInjection\Container;
use Swift_Mailer;
use Swift_Message;
use Swift_Mime_Message;
use Swift_Attachment;

class MailSender
{
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
     * @var string 
     */
    protected $addressToMockRegexp;
    
    /**
     * @var string 
     */
    protected $mockPath;
    
    /**
     * @param \AppBundle\Mailer\MailerService $apiClient
     * @param Translator $translator
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        
        $this->translator = $container->get('translator');
        $this->router = $container->get('router');
        $this->validator = $container->get('validator');
        $this->addressToMockRegexp = $this->container->hasParameter('email_mock_address') ? $this->container->getParameter('email_mock_address') : null;
        $this->mockPath = $this->container->hasParameter('email_mock_path') ? $this->container->getParameter('email_mock_path') : null;
    }

    
    /**
     * @param Email $email
     * @param array $groups
     * @return type
     * @throws \Exception
     */
    public function send(Email $email, array $groups = ['text'], $transport = 'default')
    {
        //validate change password object
        $errors = $this->validator->validate($email,$groups);
        
        if(count($errors) > 0){
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
        $mailerService = new Swift_Mailer($this->container->get($mailTransport));
        $message = $mailerService->createMessage(); /* @var $message Swift_Message */
        $message->setTo($email->getToEmail(), $email->getToName());
        $message->setFrom($email->getFromEmail(), $email->getFromName());

        $message->setSubject($email->getSubject());
        $message->setBody($email->getBodyText());
        $message->addPart($email->getBodyHtml(), 'text/html');

        foreach ($email->getAttachments() as $attachment) {
            $message->attach(new Swift_Attachment($attachment->getContent(), $attachment->getFilename(), $attachment->getContentType()));
        }

        // behat-@ emails goes to file instead of real sending
        if ($this->isEmailToMock($message)) {
            $result = $this->prependMessageIntoEmailMockPath($message);
        } else {
            $result = $mailerService->send($message);
        }

        return ['result' => $result];
    }

    /**
     * @param Swift_Mime_Message $message
     * @return boolean
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
     * @param Swift_Mime_Message $swiftMessage
     * 
     * @return string with debug info
     */
    private function prependMessageIntoEmailMockPath(Swift_Message $swiftMessage)
    {
        // prepend email into the file
        $emails =  $this->getEmailsFromFile();
        
        array_unshift($emails, MessageUtils::messageToArray($swiftMessage));
        
        $ret = $this->writeEmailsToFile($emails);

        return "Email saved. $ret bytes written.";
    }
    
    /**
     * @return array
     */
    private function getEmailsFromFile()
    {
        $path = $this->container->getParameter('email_mock_path');
        
        return json_decode(file_get_contents($path), true) ?: [];
    }
    
    /**
     * @return array
     */
    private function writeEmailsToFile(array $emails)
    {
        $path = $this->container->getParameter('email_mock_path');
        
        $ret = file_put_contents($path, json_encode($emails));
        
        if (false === $ret) {
            throw new \RuntimeException("Cannot write email into $path");
        }
        
        return $ret;
    }

}
