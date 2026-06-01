<?php

namespace OPG\Digideps\Backend\v2\DTO;

class UserDto
{
    private ?int $id = null;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $email = null;
    private ?string $roleName = null;
    private ?string $address1 = null;
    private ?string $address2 = null;
    private ?string $address3 = null;
    private ?string $addressPostcode = null;
    private ?string $addressCountry = null;
    private ?bool $active = null;
    private ?string $jobTitle = null;
    private ?string $phoneMain = null;
    private ?\DateTime $lastLoggedIn = null;
    private ?array $clients = null;
    private ?int $deputyUid = null;
    private ?bool $isPrimary = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getEmail(): ?string
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

    public function setId(int $id): UserDto
    {
        $this->id = $id;

        return $this;
    }

    public function setLastLoggedIn(\DateTime $lastLoggedIn): static
    {
        $this->lastLoggedIn = $lastLoggedIn;

        return $this;
    }

    public function setFirstName(string $firstName): UserDto
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function setLastName(string $lastName): UserDto
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function setEmail(string $email): UserDto
    {
        $this->email = strtolower($email);

        return $this;
    }

    public function setRoleName(string $roleName): UserDto
    {
        $this->roleName = $roleName;

        return $this;
    }

    public function setAddressPostcode(?string $addressPostcode): UserDto
    {
        $this->addressPostcode = $addressPostcode;

        return $this;
    }

    public function setActive(bool $active): UserDto
    {
        $this->active = $active;

        return $this;
    }

    public function setJobTitle(string $jobTitle): UserDto
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function setPhoneMain(string $phoneMain): UserDto
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

    public function getDeputyUid(): ?int
    {
        return $this->deputyUid ?? null;
    }

    public function setDeputyUid(?int $deputyUid): UserDto
    {
        $this->deputyUid = $deputyUid;

        return $this;
    }

    public function getIsPrimary(): ?bool
    {
        return $this->isPrimary;
    }

    public function setIsPrimary(?bool $isPrimary): UserDto
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }
}
