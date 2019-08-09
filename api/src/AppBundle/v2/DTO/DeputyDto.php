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

    /** @var string */
    private $addressPostcode;

    /** @var bool */
    private $ndrEnabled;

    /** @var bool */
    private $active;

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
    public function getLastName(): string
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
    public function getRoleName(): string
    {
        return $this->roleName;
    }

    /**
     * @return string
     */
    public function getAddressPostcode(): string
    {
        return $this->addressPostcode;
    }

    /**
     * @return bool
     */
    public function getNdrEnabled(): bool
    {
        return $this->ndrEnabled;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return array
     */
    public function getClients(): array
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
     * @param array $clients
     * @return DeputyDto
     */
    public function setClients(array $clients): DeputyDto
    {
        $this->clients = $clients;
        return $this;
    }
}

