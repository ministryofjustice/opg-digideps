<?php
namespace AppBundle\Mailer;

use AppBundle\Mailer\Filter\MessageFilterInterface;
use Swift_Mailer as Mailer;
use Swift_Mime_Message as Message;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Mailer service
 */
class MailerService extends Mailer
{
//    /**
//     * @var array
//     */
//    protected $filters = [];
//
//    /**
//     * @var array
//     */
//    protected $from = ['noreply@example.com', 'Example'];
//
//    /**
//     * Add a message filter
//     *
//     * @param FilterInterface $filter
//     */
//    public function addFilter(MessageFilterInterface $filter)
//    {
//        $this->filters[] = $filter;
//    }

//    /**
//     * Set the sender details
//     *
//     * @param string $email
//     * @param string $name
//     */
//    public function setFrom($email, $name)
//    {
//        $this->from = [(string) $email, (string) $name];
//    }

    /**
     * Send HTML message
     *
     * This method will also send the plain text message
     * for text readers
     *
     * @param Message $message
     * @param EmailView $view
     * @param array $parameters
     *
     * @return boolean
     */
    public function sendMimeMessage(Message $message, $subject, $body)
    {
        $message->setBody($body);
        $message->addPart($body, 'text/html');

//        $message->setFrom(reset($this->from), end($this->from));
        $message->setSubject($subject);

//        foreach ($this->filters as $filter) {
//            $filter->filter($message);
//        }

        return (boolean) parent::send($message);
    }
}
