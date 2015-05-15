<?php
namespace AppBundle\Mailer\Transport;

use Swift_Transport;
use Swift_Events_EventListener;
use Swift_Mime_Message;
use SendGrid\Email;
use AppBundle\Mailer\Utils\MessageUtils;
use SendGrid;

/**
 * Mailchimp transport layer
 * To implement by looking at logic in camelot code
 *
 * @codeCoverageIgnore
 */
class SendGridTransport implements Swift_Transport
{
    /**
     * @var SendGrid 
     */
    private $sendGrid;
    
    /**
     * Needed to put attachment content into in order to be picked up by sendgrid, 
     * currently the only way to add attachments
     * @var string 
     */
    private $temporaryAttachment;
    
    /**
     * @var array 
     */
    private $emailFileWriters = [];
    
    
    public function __construct(SendGrid $sendGrid, $temporaryAttachment)
    {
        $this->sendGrid = $sendGrid;
        $this->temporaryAttachment = $temporaryAttachment;
        $this->emailFileWriters = [];
    }

    
    /**
     * @see Swift_Transport::isStarted
     */
    public function isStarted()
    {
    }

    /**
     * @param Swift_Events_EventListener $plugin
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        
    }

    private function getHtmlPart(Swift_Mime_Message $message) 
    {
        foreach ($message->getChildren() as $part) {
            if ($part->getContentType() === 'text/html') {
                return $part->getBody();
            }
        }
        
        return null;
    }
    
    /**
     * @param Swift_Mime_Message $swiftMessage
     * @param array &$failedRecipients
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function send(Swift_Mime_Message $swiftMessage, &$failedRecipients = null)
    {
        if ($path = $this->getPathFileWriterIfExists($swiftMessage)) {
            $this->writeMessageIntoFile($swiftMessage, $path);
            return;
        } 
        
        $sendGridMessage = $this->createSendGridMessageFromSwiftMessage($swiftMessage);
        $this->sendGrid->send($sendGridMessage);
    }
    
    /**
     * @param Swift_Mime_Message $swiftMessage
     * @return string|null
     */
    private function getPathFileWriterIfExists(Swift_Mime_Message $swiftMessage)
    {
        $to = $swiftMessage->getTo();
        reset($to);
        $emailAddress = key($to);
        foreach ($this->emailFileWriters as $emailRegexpr => $path) {
            if (preg_match($emailRegexpr, $emailAddress)) {
                return $path;
            }
        }
        
        return null;
    }
    
    
    /**
     * @param Swift_Mime_Message $swiftMessage
     */
    private function writeMessageIntoFile(Swift_Mime_Message $swiftMessage, $path)
    {
         $data = MessageUtils::messageToArray($swiftMessage);
         $ret = file_put_contents($path, json_encode($data));
         if (false === $ret) {
             throw new \RuntimeException("Cannot write email into $path");
         }
    }
    
    /**
     * @param Swift_Mime_Message $message
     * 
     * @return Email
     */
    protected function createSendGridMessageFromSwiftMessage(Swift_Mime_Message $message)
    {
        $email = new Email();
        
        $to = $message->getTo();
        reset($to);
        $email
            ->addTo(key($to), reset($to));
                 
        $from = $message->getFrom();
        reset($from);
        $email->setFrom(key($from), reset($from));
        
        $email
            ->setSubject($message->getSubject())
            ->setText($message->getBody())
        ;
        
        if ($html = $this->getHtmlPart($message)) {
            $email->setHtml($html);
        }
        
        // add attachments
        foreach ($message->getChildren() as $children) { /* @var $children \Swift_Mime_MimeEntity */
            file_put_contents($this->temporaryAttachment, $children->getBody());
            $email->addAttachment($this->temporaryAttachment);
        }
        
        return $email;
    }

    /**
     */
    public function start()
    {
    }

    /**
    */
    public function stop()
    {
    }

    /**
     * @param string $email
     * @param string $path
     */
    public function addEmailFileWriter($email, $path)
    {
        $this->emailFileWriters[$email] = $path;
    }
    
    /**
     * @return array
     */
    public function getEmailFileWriters()
    {
        return $this->emailFileWriters;
    }

}