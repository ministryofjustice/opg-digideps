<?php

namespace AppBundle\v2\DTO;

class NamedDeputyDto
{
    /** @var int */
    private $id;

    /** @var string */
    private $deputyNo;

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
    private $depAddrNo;

    /** @var  string */
    private $phoneMain;

    /** @var  string */
    private $phoneAlterrnative;

    /** @var  string */
    private $address1;

    /** @var  string */
    private $address2;

    /** @var  string */
    private $address3;

    /** @var  string */
    private $address4;

    /** @var  string */
    private $address5;

    /** @var string */
    private $addressPostcode;

    /** @var string */
    private $addressCountry;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeputyNo(): string
    {
        return $this->deputyNo;
    }

    /**
     * @param string $deputyNo
     *
     * @return $this
     */
    public function setDeputyNo(string $deputyNo)
    {
        $this->deputyNo = $deputyNo;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return $this
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail1(): string
    {
        return $this->email1;
    }

    /**
     * @param string $email1
     *
     * @return $this
     */
    public function setEmail1(string $email1)
    {
        $this->email1 = $email1;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail2(): ?string
    {
        return $this->email2;
    }

    /**
     * @param string $email2
     *
     * @return $this
     */
    public function setEmail2(string $email2)
    {
        $this->email2 = $email2;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail3(): ?string
    {
        return $this->email3;
    }

    /**
     * @param string $email3
     *
     * @return $this
     */
    public function setEmail3(string $email3)
    {
        $this->email3 = $email3;
        return $this;
    }

    /**
     * @return string
     */
    public function getDepAddrNo(): ?string
    {
        return $this->depAddrNo;
    }

    /**
     * @param string $depAddrNo
     * @return $this
     */
    public function setDepAddrNo(string $depAddrNo)
    {
        $this->depAddrNo = $depAddrNo;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneMain(): ?string
    {
        return $this->phoneMain;
    }

    /**
     * @param string $phoneMain
     *
     * @return $this
     */
    public function setPhoneMain(string $phoneMain)
    {
        $this->phoneMain = $phoneMain;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneAlterrnative(): ?string
    {
        return $this->phoneAlterrnative;
    }

    /**
     * @param string $phoneAlterrnative
     *
     * @return $this
     */
    public function setPhoneAlterrnative(string $phoneAlterrnative)
    {
        $this->phoneAlterrnative = $phoneAlterrnative;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     *
     * @return $this
     */
    public function setAddress1(string $address1)
    {
        $this->address1 = $address1;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     *
     * @return $this
     */
    public function setAddress2(string $address2)
    {
        $this->address2 = $address2;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    /**
     * @param string $address3
     *
     * @return $this
     */
    public function setAddress3(string $address3)
    {
        $this->address3 = $address3;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress4(): ?string
    {
        return $this->address4;
    }

    /**
     * @param string $address4
     *
     * @return $this
     */
    public function setAddress4(string $address4)
    {
        $this->address4 = $address4;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress5(): ?string
    {
        return $this->address5;
    }

    /**
     * @param string $address5
     *
     * @return $this
     */
    public function setAddress5(string $address5)
    {
        $this->address5 = $address5;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressPostcode(): ?string
    {
        return $this->addressPostcode;
    }

    /**
     * @param string $addressPostcode
     *
     * @return $this
     */
    public function setAddressPostcode(string $addressPostcode)
    {
        $this->addressPostcode = $addressPostcode;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    /**
     * @param string $addressCountry
     *
     * @return $this
     */
    public function setAddressCountry(string $addressCountry)
    {
        $this->addressCountry = $addressCountry;
        return $this;
    }

}

