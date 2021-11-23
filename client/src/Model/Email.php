<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Email
{
    /**
     * @Assert\NotBlank(message="sendEmail.toEmail.notBlank", groups={"html","text"})
     * @Assert\Email(message="sendEmail.toEmail.invalid", groups={"html","text"})
     */
    private $toEmail;

    /**
     * @Assert\NotBlank(message="sendEmail.fromName.notBlank", groups={"html","text"})
     * @Assert\Type(type="string", message="sendEmail.fromName.invalid", groups={"html","text"})
     */
    private $fromName;

    private $fromEmailNotifyID;

    /**
     * @Assert\NotBlank(message="sendEmail.subject.notBlank", groups={"html","text"})
     */
    private $subject;

    private $template;

    private $parameters;

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
     * @return \App\Model\Email
     */
    public function setToEmail($toEmail)
    {
        $this->toEmail = $toEmail;

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
     * @return \App\Model\Email
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
     * @return \App\Model\Email
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getFromEmailNotifyID(): ?string
    {
        return $this->fromEmailNotifyID;
    }

    /**
     * @param mixed $fromEmailNotifyID
     *
     * @return Email
     */
    public function setFromEmailNotifyID($fromEmailNotifyID): self
    {
        $this->fromEmailNotifyID = $fromEmailNotifyID;

        return $this;
    }
}
