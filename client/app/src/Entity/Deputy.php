<?php

namespace OPG\Digideps\Frontend\Entity;

use JMS\Serializer\Annotation as JMS;

/**
 * Deputy.
 */
class Deputy implements DeputyInterface
{
    /**
     * @var int
     */
    #[JMS\Type('integer')]
    private $id;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $deputyUid;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $firstname;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $lastname;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $email1;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $email2;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $email3;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $address1;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $address2;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $address3;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $address4;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $address5;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $addressPostcode;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $addressCountry;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $phoneMain;

    /**
     * @var string
     */
    #[JMS\Type('string')]
    private $phoneAlternative;

    #[JMS\Type('OPG\Digideps\Frontend\Entity\User')]
    public ?User $user = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDeputyUid(): string
    {
        return $this->deputyUid;
    }

    public function setDeputyUid(string $deputyUid): static
    {
        $this->deputyUid = $deputyUid;

        return $this;
    }

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
    public function setFirstname($firstname): static
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
    public function setLastname($lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * @return string
     */
    public function getEmail1()
    {
        return $this->email1;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email1;
    }

    /**
     * @param string $email1
     */
    public function setEmail1($email1): static
    {
        $this->email1 = $email1;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail2()
    {
        return $this->email2;
    }

    /**
     * @param string $email2
     */
    public function setEmail2($email2): static
    {
        $this->email2 = $email2;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail3()
    {
        return $this->email3;
    }

    /**
     * @param string $email3
     */
    public function setEmail3($email3): static
    {
        $this->email3 = $email3;

        return $this;
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
    public function getAddress4()
    {
        return $this->address4;
    }

    /**
     * @param string $address4
     */
    public function setAddress4($address4): static
    {
        $this->address4 = $address4;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress5()
    {
        return $this->address5;
    }

    /**
     * @param string $address5
     */
    public function setAddress5($address5): static
    {
        $this->address5 = $address5;

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
     * @return array
     */
    public function getAddressNotEmptyParts(): array
    {
        return array_filter([
            $this->address1,
            $this->address2,
            $this->address3,
            $this->address4,
            $this->address5,
            $this->addressPostcode,
            $this->addressCountry,
        ]);
    }

    /**
     * @return string
     */
    public function getPhoneMain()
    {
        return $this->phoneMain;
    }

    /**
     * @param string $phoneMain
     */
    public function setPhoneMain($phoneMain): static
    {
        $this->phoneMain = trim($phoneMain);

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneAlternative()
    {
        return $this->phoneAlternative;
    }

    /**
     * @param string $phoneAlternative
     */
    public function setPhoneAlternative($phoneAlternative): static
    {
        $this->phoneAlternative = trim($phoneAlternative);

        return $this;
    }

    public function getLastLoggedIn(): ?\DateTime
    {
        if (is_null($this->user)) {
            return null;
        }

        return $this->user->getLastLoggedIn();
    }
}
