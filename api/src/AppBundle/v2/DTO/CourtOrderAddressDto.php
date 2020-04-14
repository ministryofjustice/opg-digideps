<?php

namespace AppBundle\v2\DTO;

class CourtOrderAddressDto
{
    /** @var string|null */
    private $addressLine1;

    /** @var string|null */
    private $addressLine2;

    /** @var string|null */
    private $addressLine3;

    /** @var string|null */
    private $town;

    /** @var string|null */
    private $county;

    /** @var string|null */
    private $postcode;

    /** @var string|null */
    private $country;

    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function getAddressLine3(): ?string
    {
        return $this->addressLine3;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setAddressLine1(?string $addressLine1): CourtOrderAddressDto
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    public function setAddressLine2(?string $addressLine2): CourtOrderAddressDto
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    public function setAddressLine3(?string $addressLine3): CourtOrderAddressDto
    {
        $this->addressLine3 = $addressLine3;

        return $this;
    }

    public function setTown(?string $town): CourtOrderAddressDto
    {
        $this->town = $town;

        return $this;
    }

    public function setCounty(?string $county): CourtOrderAddressDto
    {
        $this->county = $county;

        return $this;
    }

    public function setPostcode(?string $postcode): CourtOrderAddressDto
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function setCountry(?string $country): CourtOrderAddressDto
    {
        $this->country = $country;

        return $this;
    }
}
