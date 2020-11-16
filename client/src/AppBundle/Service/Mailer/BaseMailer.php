<?php declare(strict_types=1);


namespace AppBundle\Service\Mailer;

abstract class BaseMailer
{
    /** @var MailFactory */
    protected $mailFactory;

    /** @var MailSender */
    protected $mailSender;

    public function setMailFactory(MailFactory $mailFactory)
    {
        $this->mailFactory = $mailFactory;
        return $this;
    }

    public function setMailSender(MailSender $mailSender)
    {
        $this->mailSender = $mailSender;
        return $this;
    }
}
