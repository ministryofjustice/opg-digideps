<?php declare(strict_types=1);


namespace AppBundle\Service\Mailer;

class Mailer
{
    /** @var MailFactory */
    protected $mailFactory;

    /** @var MailSender */
    protected $mailSender;

    public function __construct(MailFactory $mailFactory, MailSender $mailSender)
    {
        $this->mailFactory = $mailFactory;
        $this->mailSender = $mailSender;
    }

    /**
     * @return MailFactory
     */
    public function getMailFactory(): MailFactory
    {
        return $this->mailFactory;
    }

    /**
     * @return MailSender
     */
    public function getMailSender(): MailSender
    {
        return $this->mailSender;
    }

    //Look at wrapping up all email sending in this class rather than exposing the functions and then injecting Mailer into classes
}
