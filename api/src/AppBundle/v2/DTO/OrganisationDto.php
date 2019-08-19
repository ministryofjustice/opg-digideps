<?php

namespace AppBundle\v2\DTO;

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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return OrganisationDto
     */
    public function setId(int $id): OrganisationDto
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return OrganisationDto
     */
    public function setName(string $name): OrganisationDto
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailIdentifier(): string
    {
        return $this->emailIdentifier;
    }

    /**
     * @param string $emailIdentifier
     * @return OrganisationDto
     */
    public function setEmailIdentifier(string $emailIdentifier): OrganisationDto
    {
        $this->emailIdentifier = $emailIdentifier;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->isActivated;
    }

    /**
     * @param bool $isActivated
     * @return OrganisationDto
     */
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

    /**
     * @param array $users
     * @return OrganisationDto
     */
    public function setUsers(array $users): OrganisationDto
    {
        $this->users = $users;
        return $this;
    }
}
