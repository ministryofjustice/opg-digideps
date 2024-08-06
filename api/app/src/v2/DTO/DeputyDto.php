<?php

namespace App\v2\DTO;

class DeputyDto
{
    /** @var int */
    private $id;

    /** @var string */
    private $deputyUid;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $email1;

    /** @var string */
    private $email2;

    /** @var string */
    private $email3;

    /** @var string */
    private $phoneMain;

    /** @var string */
    private $phoneAlterrnative;

    /** @var string */
    private $address1;

    /** @var string */
    private $address2;

    /** @var string */
    private $address3;

    /** @var string */
    private $address4;

    /** @var string */
    private $address5;

    /** @var string */
    private $addressPostcode;

    /** @var string */
    private $addressCountry;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    public function getDeputyUid(): string
    {
        return $this->deputyUid;
    }

    /**
     * @return $this
     */
    public function setDeputyUid(string $deputyUid)
    {
        $this->deputyUid = $deputyUid;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return $this
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return $this
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail1(): string
    {
        return $this->email1;
    }

    /**
     * @return $this
     */
    public function setEmail1(string $email1)
    {
        $this->email1 = $email1;

        return $this;
    }

    public function getEmail2(): ?string
    {
        return $this->email2;
    }

    /**
     * @return $this
     */
    public function setEmail2(string $email2)
    {
        $this->email2 = $email2;

        return $this;
    }

    public function getEmail3(): ?string
    {
        return $this->email3;
    }

    /**
     * @return $this
     */
    public function setEmail3(string $email3)
    {
        $this->email3 = $email3;

        return $this;
    }

    public function getPhoneMain(): ?string
    {
        return $this->phoneMain;
    }

    /**
     * @return $this
     */
    public function setPhoneMain(string $phoneMain)
    {
        $this->phoneMain = $phoneMain;

        return $this;
    }

    public function getPhoneAlterrnative(): ?string
    {
        return $this->phoneAlterrnative;
    }

    /**
     * @return $this
     */
    public function setPhoneAlterrnative(string $phoneAlterrnative)
    {
        $this->phoneAlterrnative = $phoneAlterrnative;

        return $this;
    }

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    /**
     * @return $this
     */
    public function setAddress1(string $address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    /**
     * @return $this
     */
    public function setAddress2(string $address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    /**
     * @return $this
     */
    public function setAddress3(string $address3)
    {
        $this->address3 = $address3;

        return $this;
    }

    public function getAddress4(): ?string
    {
        return $this->address4;
    }

    /**
     * @return $this
     */
    public function setAddress4(string $address4)
    {
        $this->address4 = $address4;

        return $this;
    }

    public function getAddress5(): ?string
    {
        return $this->address5;
    }

    /**
     * @return $this
     */
    public function setAddress5(string $address5)
    {
        $this->address5 = $address5;

        return $this;
    }

    public function getAddressPostcode(): ?string
    {
        return $this->addressPostcode;
    }

    /**
     * @return $this
     */
    public function setAddressPostcode(string $addressPostcode)
    {
        $this->addressPostcode = $addressPostcode;

        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    /**
     * @return $this
     */
    public function setAddressCountry(string $addressCountry)
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }
}
