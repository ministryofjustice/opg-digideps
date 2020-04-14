<?php

namespace AppBundle\v2\DTO;

use DateTime;

class CourtOrderDeputyDto
{
    /** @var string */
    private $deputyNumber;

    /** @var string */
    private $firstname;

    /** @var string */
    private $surname;

    /** @var string|null */
    private $email;

    /** @var DateTime|null */
    private $dob;

    /** @var CourtOrderAddressDto */
    private $address;

    public function getDeputyNumber(): string
    {
        return $this->deputyNumber;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getDob(): ?DateTime
    {
        return $this->dob;
    }

    public function getAddress(): CourtOrderAddressDto
    {
        return $this->address;
    }

    public function setDeputyNumber(string $deputyNumber): CourtOrderDeputyDto
    {
        $this->deputyNumber = $deputyNumber;

        return $this;
    }

    public function setFirstname(string $firstname): CourtOrderDeputyDto
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function setSurname(string $surname): CourtOrderDeputyDto
    {
        $this->surname = $surname;

        return $this;
    }

    public function setEmail(?string $email): CourtOrderDeputyDto
    {
        $this->email = $email;

        return $this;
    }

    public function setDob(?DateTime $dob): CourtOrderDeputyDto
    {
        $this->dob = $dob;

        return $this;
    }

    public function setAddress(CourtOrderAddressDto $address): CourtOrderDeputyDto
    {
        $this->address = $address;

        return $this;
    }
}
