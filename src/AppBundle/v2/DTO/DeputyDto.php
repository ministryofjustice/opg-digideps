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

    /** @var array */
    private $clients;

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
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * @return string
     */
    public function getAddressPostcode()
    {
        return $this->addressPostcode;
    }

    /**
     * @return bool
     */
    public function getNdrEnabled()
    {
        return $this->ndrEnabled;
    }

    /**
     * @return array
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * @param int $id
     * @return DeputyDto
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $firstName
     * @return DeputyDto
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @param string $lastName
     * @return DeputyDto
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @param string $email
     * @return DeputyDto
     */
    public function setEmail($email)
    {
        $this->email = strtolower($email);
        return $this;
    }

    /**
     * @param string $roleName
     * @return DeputyDto
     */
    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;
        return $this;
    }

    /**
     * @param string $addressPostcode
     * @return DeputyDto
     */
    public function setAddressPostcode($addressPostcode)
    {
        $this->addressPostcode = $addressPostcode;
        return $this;
    }

    /**
     * @param bool $ndrEnabled
     * @return DeputyDto
     */
    public function setNdrEnabled($ndrEnabled)
    {
        $this->ndrEnabled = $ndrEnabled;
        return $this;
    }

    /**
     * @param array $clients
     * @return DeputyDto
     */
    public function setClients(array $clients)
    {
        $this->clients = $clients;
        return $this;
    }
}

