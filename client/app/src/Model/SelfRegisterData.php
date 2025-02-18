<?php

namespace App\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SelfRegisterData.
 */
class SelfRegisterData
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank( message="user.firstname.notBlank" )
     *
     * @Assert\Length(min=2, max=50, minMessage="user.firstname.minLength", maxMessage="user.firstname.maxLength" )
     */
    private $firstname;

    /**
     * @var string lastname
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(message="user.lastname.notBlank" )
     *
     * @Assert\Length(min=2, max=50, minMessage="user.lastname.minLength", maxMessage="user.lastname.maxLength" )
     */
    private $lastname;

    /**
     * @var string email
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank( message="user.email.notBlank")
     *
     * @Assert\Length( max=60, maxMessage="user.email.maxLength" )
     *
     * @Assert\Email( message="user.email.invalid",  )
     */
    private $email;

    /**
     * @var string email
     *
     * @JMS\Type("string")
     *
     * @Assert\Length(min=2, max=10, minMessage="user.addressPostcode.minLength", maxMessage="user.addressPostcode.maxLength" )
     */
    private $postcode;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank( message="client.firstname.notBlank" )
     *
     * @Assert\Length(min = 2, minMessage= "client.firstname.minMessage", max=50, maxMessage= "client.firstname.maxMessage")
     */
    private $clientFirstname;

    /**
     * @var string clientLastName
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank( message="client.lastname.notBlank" )
     *
     * @Assert\Length(min = 2, minMessage= "client.lastname.minMessage", max=50, maxMessage= "client.lastname.maxMessage")
     */
    private $clientLastname;

    /**
     * @var string caseNumber
     *
     * @JMS\Type("string")
     *
     * @JMS\Groups({"caseNumber"})
     *
     * @Assert\NotBlank( message="client.caseNumber.notBlank")
     *
     * @Assert\Regex(
     *      pattern="/^.{8}$|^.{10}$/",
     *      message="client.caseNumber.exactMessage1"
     *  )
     */
    private $caseNumber;

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     */
    public function setPostcode($postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientFirstname()
    {
        return $this->clientFirstname;
    }

    /**
     * @param string $clientFirstname
     */
    public function setClientFirstname($clientFirstname): self
    {
        $this->clientFirstname = $clientFirstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientLastname()
    {
        return $this->clientLastname;
    }

    /**
     * @param string $clientLastname
     */
    public function setClientLastname($clientLastname): self
    {
        $this->clientLastname = $clientLastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    /**
     * @param string $caseNumber
     */
    public function setCaseNumber($caseNumber): self
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }
}
