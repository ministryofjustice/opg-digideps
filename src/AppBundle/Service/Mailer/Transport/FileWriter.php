<?php

namespace AppBundle\Service\Mailer\Transport;

use AppBundle\Service\Mailer\MessageUtils;
use Swift_Events_EventListener;
use Swift_Mime_Message;
use Swift_Transport;

/**
 * Mock transport used for BDD testing.
 *
 * saves email into a class property, no real mail are sent
 */
class FileWriter implements Swift_Transport
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @var bool
     */
    protected $started;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @see Swift_Transport::isStarted
     *
     * @return bool always true
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * @param Swift_Events_EventListener $plugin
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
    }

    /**
     * Just adds message to internal property.
     *
     * @param Swift_Mime_Message $message
     * @param array              &$failedRecipients
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @see Swift_Transport::send
     */
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $data = MessageUtils::messageToArray($message);

        file_put_contents($this->path, json_encode($data) . PHP_EOL, FILE_APPEND);

        $this->messages[] = $message;
    }

    /**
     * Empty implementation.
     *
     * @see Swift_Transport::start
     */
    public function start()
    {
        file_put_contents($this->path, '');
        $this->started = true;
    }

    /**
     * Empty implementation.
     *
     * @see Swift_Transport::stop
     * @codeCoverageIgnore
     */
    public function stop()
    {
    }

    /**
     * Get messages added with "send".
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = $this->messages;

        if (empty($messages)) {
            $messages = array_map(function ($msg) {
                return MessageUtils::arrayToMessage(json_decode($msg, true));
            }, explode(PHP_EOL, trim(file_get_contents($this->path))));
        }

        return $messages;
    }

    /**
     * Return message if sent.
     *
     * @param type $subject
     * @param type $to
     *
     * @return Swift_Message|null
     */
    public function findMessage($subject, $to)
    {
        foreach ($this->getMessages() as $message) {
            if ($message->getSubject() == $subject && array_key_exists($to, $message->getTo())) {
                return $message;
            }
        }

        return;
    }
}
