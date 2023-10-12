<?php

namespace App\v2\DTO;

class OrganisationDto
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $emailIdentifier;

    /** @var bool */
    private $isActivated;

    /** @var array */
    private $users;

    /** @var array */
    private $clients;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): OrganisationDto
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): OrganisationDto
    {
        $this->name = $name;

        return $this;
    }

    public function getEmailIdentifier(): string
    {
        return $this->emailIdentifier;
    }

    public function setEmailIdentifier(string $emailIdentifier): OrganisationDto
    {
        $this->emailIdentifier = $emailIdentifier;

        return $this;
    }

    public function isActivated(): bool
    {
        return $this->isActivated;
    }

    public function setIsActivated(bool $isActivated): OrganisationDto
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function setUsers(array $users): OrganisationDto
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return array
     */
    public function getClients()
    {
        return $this->clients;
    }

    public function setClients(array $clients): OrganisationDto
    {
        $this->clients = $clients;

        return $this;
    }
}
