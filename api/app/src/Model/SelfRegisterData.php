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
     * @Assert\NotBlank( message="user.firstname.notBlank", groups={"self_registration", "verify_codeputy"} )
     *
     * @Assert\Length(min=2, max=50, minMessage="user.firstname.minLength", maxMessage="user.firstname.maxLength", groups={"self_registration", "verify_codeputy"} )
     */
    private $firstname;

    /**
     * @var string lastname
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank(message="user.lastname.notBlank", groups={"self_registration", "verify_codeputy"} )
     *
     * @Assert\Length(min=2, max=50, minMessage="user.lastname.minLength", maxMessage="user.lastname.maxLength", groups={"self_registration", "verify_codeputy"} )
     */
    private $lastname;

    /**
     * @var string email
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank( message="user.email.notBlank", groups={"self_registration", "verify_codeputy"})
     *
     * @Assert\Email( message="user.email.invalid", groups={"self_registration", "verify_codeputy"} )
     *
     * @Assert\Length( max=60, maxMessage="user.email.maxLength", groups={"self_registration", "verify_codeputy"} )
     */
    private $email;

    /**
     * @var string email
     *
     * @JMS\Type("string")
     *
     * @Assert\Length( max=10, maxMessage="user.addressPostcode.maxLength", groups={"self_registration", "verify_codeputy"})
     */
    private $postcode;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank( message="client.firstname.notBlank", groups={"self_registration"} )
     *
     * @Assert\Length(min = 2, minMessage= "client.firstname.minMessage", max=50, maxMessage= "client.firstname.maxMessage", groups={"self_registration"})
     */
    private $clientFirstname;

    /**
     * @var string clientLastName
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank( message="client.lastname.notBlank", groups={"self_registration", "verify_codeputy"} )
     *
     * @Assert\Length(min = 2, minMessage= "client.lastname.minMessage", max=50, maxMessage= "client.lastname.maxMessage", groups={"self_registration", "verify_codeputy"})
     */
    private $clientLastname;

    /**
     * @var string caseNumber
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank( message="client.caseNumber.notBlank", groups={"self_registration", "verify_codeputy"})
     *
     * @Assert\Regex(
     *    pattern="/^.{8}$|^.{10}$/",
     *    message="client.caseNumber.exactMessage1",
     *    groups={"self_registration", "verify_codeputy"})
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
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
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
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
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
    public function setEmail($email)
    {
        $this->email = strtolower($email);
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
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
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
    public function setClientFirstname($clientFirstname)
    {
        $this->clientFirstname = $clientFirstname;
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
    public function setClientLastname($clientLastname)
    {
        $this->clientLastname = $clientLastname;
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
    public function setCaseNumber($caseNumber)
    {
        $this->caseNumber = $caseNumber;
    }

    public function toArray()
    {
        return [
            'deputy_firstname' => $this->firstname,
            'deputy_lastname' => $this->lastname,
            // 'deputy_email' => $this->email,
            'deputy_postcode' => $this->postcode,
            'client_lastname' => $this->clientLastname,
            'client_case_number' => $this->caseNumber,
        ];
    }

    /**
     * Function to replace known Unicode chars with equivalent ASCII
     * Used to better support matching user inputted data with data present in Pre-registration table.
     */
    public function replaceUnicodeChars()
    {
        $this->firstname = str_replace('’', '\'', $this->firstname);
        $this->lastname = str_replace('’', '\'', $this->lastname);
        $this->clientFirstname = str_replace('’', '\'', $this->clientFirstname);
        $this->clientLastname = str_replace('’', '\'', $this->clientLastname);
    }
}
