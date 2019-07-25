<?php

namespace AppBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Email
{
    /**
     * @Assert\NotBlank(message="sendEmail.toEmail.notBlank", groups={"html","text"})
     * @Assert\Email(message="sendEmail.toEmail.invalid", groups={"html","text"})
     */
    private $toEmail;

    /**
     * @Assert\NotBlank(message="sendEmail.toName.notBlank", groups={"html","text"})
     * @Assert\Type(type="string", message="sendEmail.toName.invalid", groups={"html","text"})
     */
    private $toName;

    /**
     * @Assert\NotBlank(message="sendEmail.fromEmail.notBlank", groups={"html","text"})
     * @Assert\Email(message="sendEmail.fromEmail.invalid", groups={"html","text"})
     */
    private $fromEmail;

    /**
     * @Assert\NotBlank(message="sendEmail.fromName.notBlank", groups={"html","text"})
     * @Assert\Type(type="string", message="sendEmail.fromName.invalid", groups={"html","text"})
     */
    private $fromName;

    /**
     * @Assert\NotBlank(message="sendEmail.subject.notBlank", groups={"html","text"})
     */
    private $subject;

    /**
     * @Assert\NotBlank(message="sendEmail.bodyText.notBlank", groups={"text"})
     */
    private $bodyText;

    /**
     * @Assert\NotBlank(message="sendEmail.bodyHtml.notBlank", groups={"html"})
     */
    private $bodyHtml;

    private $attachments = [];

    /**
     * @return string $email
     */
    public function getToEmail()
    {
        return $this->toEmail;
    }

    /**
     * @param string $toEmail
     *
     * @return \AppBundle\Model\Email
     */
    public function setToEmail($toEmail)
    {
        $this->toEmail = $toEmail;

        return $this;
    }

    /**
     * @param string $toName
     *
     * @return string $toName
     */
    public function getToName()
    {
        return $this->toName;
    }

    /**
     * @param string $toName
     *
     * @return \AppBundle\Model\Email
     */
    public function setToName($toName)
    {
        $this->toName = $toName;

        return $this;
    }

    /**
     * @return string $fromEmail
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    /**
     * @param string $fromEmail
     *
     * @return \AppBundle\Model\Email
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    /**
     * @return string $fromName
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     *
     * @return \AppBundle\Model\Email
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * @return string $subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return \AppBundle\Model\Email
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string $bodyText
     */
    public function getBodyText()
    {
        return $this->bodyText;
    }

    /**
     * @param string $bodyText
     */
    public function setBodyText($bodyText)
    {
        $this->bodyText = $bodyText;

        return $this;
    }

    /**
     * @return string $bodyHtml
     */
    public function getBodyHtml()
    {
        return $this->bodyHtml;
    }

    /**
     * @param string $bodyHtml
     *
     * @return \AppBundle\Model\Email
     */
    public function setBodyHtml($bodyHtml)
    {
        $this->bodyHtml = $bodyHtml;

        return $this;
    }

    public function setAttachments(array $attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * @return EmailAttachment[]
     */
    public function getAttachments()
    {
        return $this->attachments;
    }
}
