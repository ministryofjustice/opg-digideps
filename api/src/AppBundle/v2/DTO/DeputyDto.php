<?php

namespace AppBundle\v2\DTO;

class DeputyDto
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

    /** @var array */
    private $clients;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    /**
     * @return string
     */
    public function getAddressPostcode(): ?string
    {
        return $this->addressPostcode;
    }

    /**
     * @return bool
     */
    public function getNdrEnabled(): ?bool
    {
        return $this->ndrEnabled;
    }

    /**
     * @return bool
     */
    public function isActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    /**
     * @return string
     */
    public function getPhoneMain(): ?string
    {
        return $this->phoneMain;
    }

    /**
     * @return array
     */
    public function getClients(): ?array
    {
        return $this->clients;
    }

    /**
     * @param int $id
     * @return DeputyDto
     */
    public function setId($id): DeputyDto
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $firstName
     * @return DeputyDto
     */
    public function setFirstName($firstName): DeputyDto
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @param string $lastName
     * @return DeputyDto
     */
    public function setLastName($lastName): DeputyDto
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @param string $email
     * @return DeputyDto
     */
    public function setEmail($email): DeputyDto
    {
        $this->email = strtolower($email);
        return $this;
    }

    /**
     * @param string $roleName
     * @return DeputyDto
     */
    public function setRoleName($roleName): DeputyDto
    {
        $this->roleName = $roleName;
        return $this;
    }

    /**
     * @param string $addressPostcode
     * @return DeputyDto
     */
    public function setAddressPostcode($addressPostcode): DeputyDto
    {
        $this->addressPostcode = $addressPostcode;
        return $this;
    }

    /**
     * @param bool $ndrEnabled
     * @return DeputyDto
     */
    public function setNdrEnabled($ndrEnabled): DeputyDto
    {
        $this->ndrEnabled = $ndrEnabled;
        return $this;
    }

    /**
     * @param bool $active
     * @return DeputyDto
     */
    public function setActive(bool $active): DeputyDto
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @param string $jobTitle
     * @return DeputyDto
     */
    public function setJobTitle($jobTitle): DeputyDto
    {
        $this->jobTitle = $jobTitle;
        return $this;
    }

    /**
     * @param string $phoneMain
     * @return DeputyDto
     */
    public function setPhoneMain($phoneMain): DeputyDto
    {
        $this->phoneMain = $phoneMain;
        return $this;
    }

    /**
     * @param array $clients
     * @return DeputyDto
     */
    public function setClients(array $clients): DeputyDto
    {
        $this->clients = $clients;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    /**
     * @param string|null $address1
     * @return DeputyDto
     */
    public function setAddress1(?string $address1): DeputyDto
    {
        $this->address1 = $address1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    /**
     * @param string|null $address2
     * @return DeputyDto
     */
    public function setAddress2(?string $address2): DeputyDto
    {
        $this->address2 = $address2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    /**
     * @param string|null $address3
     * @return DeputyDto
     */
    public function setAddress3(?string $address3): DeputyDto
    {
        $this->address3 = $address3;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    /**
     * @param string|null $addressCountry
     * @return DeputyDto
     */
    public function setAddressCountry(?string $addressCountry): DeputyDto
    {
        $this->addressCountry = $addressCountry;
        return $this;
    }
}
