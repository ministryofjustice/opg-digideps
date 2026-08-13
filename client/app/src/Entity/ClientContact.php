<?php

namespace OPG\Digideps\Frontend\Entity;

use OPG\Digideps\Frontend\Entity\Traits\CreationAudit;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ClientContact
{
    use CreationAudit;


    /**
     * @var int
     */
    #[JMS\Type('string')]
    private $id;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['add_clientcontact', 'edit_clientcontact'])]
    private $address1;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['add_clientcontact', 'edit_clientcontact'])]
    private $address2;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['add_clientcontact', 'edit_clientcontact'])]
    private $address3;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['add_clientcontact', 'edit_clientcontact'])]
    #[Assert\Length(max: 10, maxMessage: 'clientContact.form.postcode.maxMessage', groups: ['edit_clientcontact', 'add_clientcontact'])]
    private $addressPostcode;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['add_clientcontact', 'edit_clientcontact'])]
    private $addressCountry;

    /**
     * @var string
     */
    #[JMS\Type('OPG\Digideps\Frontend\Entity\Client')]
    private $client;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['add_clientcontact', 'edit_clientcontact'])]
    #[Assert\Email(message: 'clientContact.form.email.invalid', groups: ['add_clientcontact', 'edit_clientcontact'])]
    #[Assert\Length(max: 60, maxMessage: 'clientContact.form.email.maxLength', groups: ['add_clientcontact', 'edit_clientcontact'])]
    private $email;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['add_clientcontact', 'edit_clientcontact'])]
    #[Assert\NotBlank(message: 'clientContact.form.firstname.notBlank', groups: ['edit_clientcontact', 'add_clientcontact'])]
    #[Assert\Length(min: 2, max: 10, minMessage: 'clientContact.form.firstname.minMessage', maxMessage: 'clientContact.form.firstname.maxMessage', groups: ['edit_clientcontact', 'add_clientcontact'])]
    private $firstName;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['add_clientcontact', 'edit_clientcontact'])]
    #[Assert\Length(min: 2, max: 100, minMessage: 'clientContact.form.firstname.minMessage', maxMessage: 'clientContact.form.firstname.maxMessage')]
    private $jobTitle;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['add_clientcontact', 'edit_clientcontact'])]
    #[Assert\NotBlank(message: 'clientContact.form.lastname.notBlank', groups: ['edit_clientcontact', 'add_clientcontact'])]
    #[Assert\Length(min: 2, max: 100, minMessage: 'clientContact.form.lastname.minMessage', maxMessage: 'clientContact.form.lastname.maxMessage', groups: ['edit_clientcontact', 'add_clientcontact'])]
    private $lastName;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['add_clientcontact', 'edit_clientcontact'])]
    private $orgName;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['add_clientcontact', 'edit_clientcontact'])]
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
     */
    public function setAddress1($address1): static
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
     */
    public function setAddress2($address2): static
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
     */
    public function setAddress3($address3): static
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
     */
    public function setAddressPostcode($addressPostcode): static
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
     */
    public function setAddressCountry($addressCountry): static
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

    public function setClient(string $client): static
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

    public function setEmail(string $email): static
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
     */
    public function setFirstName($firstName): static
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
     */
    public function setJobTitle($jobTitle): static
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
     */
    public function setLastName($lastName): static
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
     */
    public function setOrgName($orgName): static
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
     */
    public function setPhone($phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}
