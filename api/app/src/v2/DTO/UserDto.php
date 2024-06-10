<?php

namespace App\v2\DTO;

class UserDto
{
    /** @var int */
    private $id;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $email;

    /** @var string */
    private $roleName;

    /** @var string|null */
    private $address1;

    /** @var string|null */
    private $address2;

    /** @var string|null */
    private $address3;

    /** @var string */
    private $addressPostcode;

    /** @var string|null */
    private $addressCountry;

    /** @var bool */
    private $ndrEnabled;

    /** @var bool */
    private $active;

    /** @var string */
    private $jobTitle;

    /** @var string */
    private $phoneMain;

    /** @var \DateTime */
    private $lastLoggedIn;

    /** @var array */
    private $clients;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    public function getAddressPostcode(): ?string
    {
        return $this->addressPostcode;
    }

    public function getNdrEnabled(): ?bool
    {
        return $this->ndrEnabled;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function getPhoneMain(): ?string
    {
        return $this->phoneMain;
    }

    public function getLastLoggedIn(): ?\DateTime
    {
        return $this->lastLoggedIn;
    }

    public function getClients(): ?array
    {
        return $this->clients;
    }

    /**
     * @param int $id
     */
    public function setId($id): UserDto
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param \DateTime $lastLoggedIn
     *
     * @return UserDto
     */
    public function setLastLoggedIn($lastLoggedIn)
    {
        $this->lastLoggedIn = $lastLoggedIn;

        return $this;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName): UserDto
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName): UserDto
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @param string $email
     */
    public function setEmail($email): UserDto
    {
        $this->email = strtolower($email);

        return $this;
    }

    /**
     * @param string $roleName
     */
    public function setRoleName($roleName): UserDto
    {
        $this->roleName = $roleName;

        return $this;
    }

    /**
     * @param string $addressPostcode
     */
    public function setAddressPostcode($addressPostcode): UserDto
    {
        $this->addressPostcode = $addressPostcode;

        return $this;
    }

    /**
     * @param bool $ndrEnabled
     */
    public function setNdrEnabled($ndrEnabled): UserDto
    {
        $this->ndrEnabled = $ndrEnabled;

        return $this;
    }

    public function setActive(bool $active): UserDto
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @param string $jobTitle
     */
    public function setJobTitle($jobTitle): UserDto
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * @param string $phoneMain
     */
    public function setPhoneMain($phoneMain): UserDto
    {
        $this->phoneMain = $phoneMain;

        return $this;
    }

    public function setClients(array $clients): UserDto
    {
        $this->clients = $clients;

        return $this;
    }

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function setAddress1(?string $address1): UserDto
    {
        $this->address1 = $address1;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): UserDto
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    public function setAddress3(?string $address3): UserDto
    {
        $this->address3 = $address3;

        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry): UserDto
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }
}
