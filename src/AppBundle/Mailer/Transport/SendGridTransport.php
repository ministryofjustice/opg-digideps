<?php
namespace AppBundle\Mailer\Transport;

use Swift_Transport;
use Swift_Events_EventListener;
use Swift_Mime_Message;
use SendGrid\Email;

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
    
    
    public function __construct(\SendGrid $sendGrid)
    {
        $this->sendGrid = $sendGrid;
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
        $email = $this->createSendGridMessageFromSwiftMessage($swiftMessage);
        
        $this->sendGrid->send($email);
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
}