<?php

namespace App\Entity;

use App\Entity\Traits\CreationAudit;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

class ClientContact
{
    use CreationAudit;


    /**
     * @var int
     *
     * @JMS\Type("string")
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"add_clientcontact", "edit_clientcontact"})
     * @AppAssert\TextNoSpecialCharacters
     */
    private $address1;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"add_clientcontact", "edit_clientcontact"})
     * @AppAssert\TextNoSpecialCharacters
     */
    private $address2;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"add_clientcontact", "edit_clientcontact"})
     * @AppAssert\TextNoSpecialCharacters
     */
    private $address3;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\Length(max=10, maxMessage="clientContact.form.postcode.maxMessage",
     *     groups={"edit_clientcontact", "add_clientcontact"})
     * @AppAssert\TextNoSpecialCharacters(groups={"edit_clientcontact", "add_clientcontact"})
     * @JMS\Groups({"add_clientcontact", "edit_clientcontact"})
     */
    private $addressPostcode;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"add_clientcontact", "edit_clientcontact"})
     * @AppAssert\TextNoSpecialCharacters(groups={"edit_clientcontact", "add_clientcontact"})
     */
    private $addressCountry;

    /**
     * @var string
     *
     * @JMS\Type("App\Entity\Client")
     * @AppAssert\TextNoSpecialCharacters
     */
    private $client;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\Email( message="clientContact.form.email.invalid", groups={"add_clientcontact", "edit_clientcontact"}, checkMX=false, checkHost=false )
     * @Assert\Length( max=60, maxMessage="clientContact.form.email.maxLength", groups={"add_clientcontact", "edit_clientcontact"} )
     *
     * @JMS\Groups({"add_clientcontact", "edit_clientcontact"})
     * @AppAssert\TextNoSpecialCharacters(groups={"add_clientcontact", "edit_clientcontact"})
     */
    private $email;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\NotBlank( message="clientContact.form.firstname.notBlank", groups={"edit_clientcontact", "add_clientcontact"})
     * @Assert\Length(min=2, max=10, minMessage="clientContact.form.firstname.minMessage", maxMessage="clientContact.form.firstname.maxMessage",
     *     groups={"edit_clientcontact", "add_clientcontact"})
     *
     * @JMS\Groups({"add_clientcontact", "edit_clientcontact"})
     * @AppAssert\TextNoSpecialCharacters(groups={"edit_clientcontact", "add_clientcontact"})
     */
    private $firstName;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\Length(min=2, max=100, minMessage="clientContact.form.firstname.minMessage", maxMessage="clientContact.form.firstname.maxMessage" )
     * @JMS\Groups({"add_clientcontact", "edit_clientcontact"})
     * @AppAssert\TextNoSpecialCharacters
     */
    private $jobTitle;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @Assert\NotBlank( message="clientContact.form.lastname.notBlank", groups={"edit_clientcontact", "add_clientcontact"})
     * @Assert\Length(min=2, max=100, minMessage="clientContact.form.lastname.minMessage", maxMessage="clientContact.form.lastname.maxMessage",
     *     groups={"edit_clientcontact", "add_clientcontact"})
     *
     * @JMS\Groups({"add_clientcontact", "edit_clientcontact"})
     * @AppAssert\TextNoSpecialCharacters(groups={"edit_clientcontact", "add_clientcontact"})
     */
    private $lastName;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"add_clientcontact", "edit_clientcontact"})
     * @AppAssert\TextNoSpecialCharacters
     */
    private $orgName;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"add_clientcontact", "edit_clientcontact"})
     * @AppAssert\TextNoSpecialCharacters
     */
    private $phone;

    /**
     * Constructor.
     */
    public function __construct(Client $client)
    {
        $this->setClient($client);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     *
     * @return string
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     *
     * @return string
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * @param string $address3
     *
     * @return string
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressPostcode()
    {
        return $this->addressPostcode;
    }

    /**
     * @param string $addressPostcode
     *
     * @return string
     */
    public function setAddressPostcode($addressPostcode)
    {
        $this->addressPostcode = $addressPostcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressCountry()
    {
        return $this->addressCountry;
    }

    /**
     * @param string $addressCountry
     *
     * @return string
     */
    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;
        return $this;
    }

    /**
     * @return string
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $client
     *
     * @return string
     */
    public function setClient($client)
    {
        $this->client = $client;
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
     *
     * @return string
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return string
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @param string $jobTitle
     *
     * @return string
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return string
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrgName()
    {
        return $this->orgName;
    }

    /**
     * @param string $orgName
     *
     * @return string
     */
    public function setOrgName($orgName)
    {
        $this->orgName = $orgName;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return string
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }
}
