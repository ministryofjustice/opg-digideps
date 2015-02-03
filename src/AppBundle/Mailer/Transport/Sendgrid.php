<?php
namespace AppBundle\Mailer\Transport;

use Swift_Transport;
use Swift_Events_EventListener;
use Swift_Mime_Message;

/**
 * Mailchimp transport layer
 * To implement by looking at logic in camelot code
 *
 * @codeCoverageIgnore
 */
class Sendgrid implements Swift_Transport
{
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

    /**
     * @param Swift_Mime_Message $message
     * @param array &$failedRecipients
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        throw new \Exception("sendgrid transport not implemented");
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