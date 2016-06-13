<?php

namespace AppBundle\Service\Mailer\Transport;

use Swift_Transport;
use Swift_Events_EventListener;
use Swift_Mime_Message;

class TransportMock implements Swift_Transport
{
    private $started = false;
    private $stopped = false;
    private $sentMessages = [];

    public function resetMockVars()
    {
        $this->started = false;
        $this->stopped = false;

        return $this;
    }

    public function isStarted()
    {
        return $this->started;
    }

    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
    }

    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $this->sentMessages[] = $message;

        return 'sent';
    }

    public function start()
    {
        $this->started = true;
    }

    public function stop()
    {
        $this->stopped = true;
    }

    /**
     * @return \Swift_Message[]
     */
    public function getSentMessages()
    {
        return $this->sentMessages;
    }
}
