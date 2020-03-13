<?php declare(strict_types=1);

namespace AppBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SelfRegisterData.
 */
class SelfRegisterData
{
    /**
     * @var string
     * @JMS\Type("string")
     * @Assert\NotBlank( message="user.firstname.notBlank", groups={"self_registration", "verify_codeputy"} )
     * @Assert\Length(min=2, max=50, minMessage="user.firstname.minLength", maxMessage="user.firstname.maxLength", groups={"self_registration", "verify_codeputy"} )
     */
    private $firstname;

    /**
     * @var string lastname
     * @JMS\Type("string")
     * @Assert\NotBlank(message="user.lastname.notBlank", groups={"self_registration", "verify_codeputy"} )
     * @Assert\Length(min=2, max=50, minMessage="user.lastname.minLength", maxMessage="user.lastname.maxLength", groups={"self_registration", "verify_codeputy"} )
     */
    private $lastname;

    /**
     * @var string email
     * @JMS\Type("string")
     * @Assert\NotBlank( message="user.email.notBlank", groups={"self_registration", "verify_codeputy"})
     * @Assert\Email( message="user.email.invalid", checkMX=false, checkHost=false, groups={"self_registration", "verify_codeputy"} )
     * @Assert\Length( max=60, maxMessage="user.email.maxLength", groups={"self_registration", "verify_codeputy"} )
     */
    private $email;

    /**
     * @var string email
     * @JMS\Type("string")
     * @Assert\Length( max=10, maxMessage="user.addressPostcode.maxLength", groups={"self_registration", "verify_codeputy"})
     */
    private $postcode;

    /**
     * @var string
     * @JMS\Type("string")
     * @Assert\NotBlank( message="client.firstname.notBlank", groups={"self_registration"} )
     * @Assert\Length(min = 2, minMessage= "client.firstname.minMessage", max=50, maxMessage= "client.firstname.maxMessage", groups={"self_registration"})
     */
    private $clientFirstname;

    /**
     * @var string clientLastName
     * @JMS\Type("string")
     * @Assert\NotBlank( message="client.lastname.notBlank", groups={"self_registration", "verify_codeputy"} )
     * @Assert\Length(min = 2, minMessage= "client.lastname.minMessage", max=50, maxMessage= "client.lastname.maxMessage", groups={"self_registration", "verify_codeputy"})
     */
    private $clientLastname;

    /**
     * @var string caseNumber
     * @JMS\Type("string")
     * @Assert\NotBlank( message="client.caseNumber.notBlank", groups={"self_registration", "verify_codeputy"})
     * @Assert\Length(min = 2, minMessage= "client.caseNumber.minMessage", max=20, maxMessage= "client.caseNumber.maxMessage", groups={"self_registration", "verify_codeputy"})
     */
    private $caseNumber;

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname): SelfRegisterData
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname): SelfRegisterData
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email): SelfRegisterData
    {
        $this->email = strtolower($email);

        return $this;
    }

    /**
     * @return string
     */
    public function getPostcode(): string
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     */
    public function setPostcode($postcode): SelfRegisterData
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientFirstname(): string
    {
        return $this->clientFirstname;
    }

    /**
     * @param string $clientFirstname
     */
    public function setClientFirstname($clientFirstname): SelfRegisterData
    {
        $this->clientFirstname = $clientFirstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientLastname(): string
    {
        return $this->clientLastname;
    }

    /**
     * @param string $clientLastname
     */
    public function setClientLastname($clientLastname): SelfRegisterData
    {
        $this->clientLastname = $clientLastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    /**
     * @param string $caseNumber
     */
    public function setCaseNumber($caseNumber): SelfRegisterData
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    /**
     * @return array<string>
     */
    public function toArray(): array
    {
        return [
            //'deputy_firstname' => $this->firstname,
            'deputy_lastname' => $this->lastname,
            //'deputy_email' => $this->email,
            'deputy_postcode' => $this->postcode,
            'client_lastname' => $this->clientLastname,
            'client_case_number' => $this->caseNumber,
        ];
    }
}
