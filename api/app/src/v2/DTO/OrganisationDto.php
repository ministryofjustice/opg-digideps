<?php

namespace OPG\Digideps\Backend\v2\DTO;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\User;

class OrganisationDto
{
    private ?int $id = null;
    private ?string $name = null;
    private ?string $emailIdentifier = null;
    private ?bool $isActivated = null;
    /** @var User[]|UserDto[]|null  */
    private ?array $users = null;
    /** @var ?Client[]  */
    private ?array $clients = null;
    private int $totalUserCount;
    private int $totalClientCount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): OrganisationDto
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): OrganisationDto
    {
        $this->name = $name;

        return $this;
    }

    public function getEmailIdentifier(): ?string
    {
        return $this->emailIdentifier;
    }

    public function setEmailIdentifier(string $emailIdentifier): OrganisationDto
    {
        $this->emailIdentifier = $emailIdentifier;

        return $this;
    }

    public function isActivated(): ?bool
    {
        return $this->isActivated;
    }

    public function setIsActivated(bool $isActivated): OrganisationDto
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    /**
     * @return User[]|UserDto[]|null
     */
    public function getUsers(): ?array
    {
        return $this->users;
    }

    /** @param User[]|UserDto[]|null $users */
    public function setUsers(?array $users): OrganisationDto
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return ?Client[]
     */
    public function getClients(): ?array
    {
        return $this->clients;
    }

    /** @param Client[] $clients */
    public function setClients(array $clients): OrganisationDto
    {
        $this->clients = $clients;

        return $this;
    }

    public function getTotalUserCount(): int
    {
        return $this->totalUserCount;
    }

    public function setTotalUserCount(int $totalUserCount): static
    {
        $this->totalUserCount = $totalUserCount;

        return $this;
    }

    public function getTotalClientCount(): int
    {
        return $this->totalClientCount;
    }

    public function setTotalClientCount(int $totalClientCount): static
    {
        $this->totalClientCount = $totalClientCount;

        return $this;
    }
}
