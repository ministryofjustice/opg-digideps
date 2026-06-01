<?php

namespace OPG\Digideps\Backend\v2\DTO;

class DeputyDto
{
    private ?int $id = null;
    private ?string $deputyUid = null;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $email1 = null;
    private ?string $email2 = null;
    private ?string $email3 = null;
    private ?string $phoneMain = null;
    private ?string $phoneAlterrnative = null;
    private ?string $address1 = null;
    private ?string $address2 = null;
    private ?string $address3 = null;
    private ?string $address4 = null;
    private ?string $address5 = null;
    private ?string $addressPostcode = null;
    private ?string $addressCountry = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getDeputyUid(): ?string
    {
        return $this->deputyUid;
    }

    public function setDeputyUid(string $deputyUid): static
    {
        $this->deputyUid = $deputyUid;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail1(): ?string
    {
        return $this->email1;
    }

    public function setEmail1(string $email1): static
    {
        $this->email1 = $email1;

        return $this;
    }

    public function getEmail2(): ?string
    {
        return $this->email2;
    }

    public function setEmail2(string $email2): static
    {
        $this->email2 = $email2;

        return $this;
    }

    public function getEmail3(): ?string
    {
        return $this->email3;
    }

    public function setEmail3(string $email3): static
    {
        $this->email3 = $email3;

        return $this;
    }

    public function getPhoneMain(): ?string
    {
        return $this->phoneMain;
    }

    public function setPhoneMain(string $phoneMain): static
    {
        $this->phoneMain = $phoneMain;

        return $this;
    }

    public function getPhoneAlterrnative(): ?string
    {
        return $this->phoneAlterrnative;
    }

    public function setPhoneAlterrnative(string $phoneAlterrnative): static
    {
        $this->phoneAlterrnative = $phoneAlterrnative;

        return $this;
    }

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function setAddress1(string $address1): static
    {
        $this->address1 = $address1;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(string $address2): static
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    public function setAddress3(string $address3): static
    {
        $this->address3 = $address3;

        return $this;
    }

    public function getAddress4(): ?string
    {
        return $this->address4;
    }

    public function setAddress4(string $address4): static
    {
        $this->address4 = $address4;

        return $this;
    }

    public function getAddress5(): ?string
    {
        return $this->address5;
    }

    public function setAddress5(string $address5): static
    {
        $this->address5 = $address5;

        return $this;
    }

    public function getAddressPostcode(): ?string
    {
        return $this->addressPostcode;
    }

    public function setAddressPostcode(string $addressPostcode): static
    {
        $this->addressPostcode = $addressPostcode;

        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(string $addressCountry): static
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }
}
