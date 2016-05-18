<?php

namespace AppBundle\Service\Mailer\Transport;

use Swift_Transport;
use Swift_Events_EventListener;
use Swift_Mime_Message;
use Swift_Attachment;
use SendGrid\Email;
use SendGrid;

/**
 * Mailchimp transport layer
 * To implement by looking at logic in camelot code.
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
     * @var string
     */
    private $temporaryAttachment;

    /**
     * @param SendGrid $sendGrid
     * @param string   $temporaryAttachment path to temp file used for attaching files
     */
    public function __construct(SendGrid $sendGrid, $temporaryAttachment)
    {
        $this->sendGrid = $sendGrid;
        $this->temporaryAttachment = $temporaryAttachment;
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

        return;
    }

    /**
     * @param Swift_Mime_Message $swiftMessage
     * @param array              &$failedRecipients
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function send(Swift_Mime_Message $swiftMessage, &$failedRecipients = null)
    {
        $sendGridMessage = $this->createSendGridMessageFromSwiftMessage($swiftMessage);
        $this->sendGrid->send($sendGridMessage);
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
            if ($children instanceof Swift_Attachment) {
                // add attachment via file (the only method supported by sendgrid)
                file_put_contents($this->temporaryAttachment, $children->getBody());
                $email->addAttachment($this->temporaryAttachment, $children->getFilename());
            }
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
        // clear temporary attachments if any
        if (file_exists($this->temporaryAttachment)) {
            unlink($this->temporaryAttachment);
        }
    }
}
