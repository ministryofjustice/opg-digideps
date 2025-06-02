<?php

namespace App\Entity\Traits;

trait AddressTrait
{
    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"user", "team", "report-submitted-by"})
     *
     * @ORM\Column(name="address1", type="string", length=200, nullable=true)
     */
    private ?string $address1 = null;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"user", "team", "report-submitted-by"})
     *
     * @ORM\Column(name="address2", type="string", length=200, nullable=true)
     */
    private ?string $address2 = null;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"user", "team", "report-submitted-by"})
     *
     * @ORM\Column(name="address3", type="string", length=200, nullable=true)
     */
    private ?string $address3 = null;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"user", "team", "report-submitted-by"})
     *
     * @ORM\Column(name="address4", type="string", length=200, nullable=true)
     */
    private ?string $address4 = null;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"user", "team", "report-submitted-by"})
     *
     * @ORM\Column(name="address5", type="string", length=200, nullable=true)
     */
    private ?string $address5 = null;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"user", "team", "report-submitted-by"})
     *
     * @ORM\Column(name="address_postcode", type="string", length=10, nullable=true)
     */
    private ?string $addressPostcode = null;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"user", "team", "report-submitted-by"})
     *
     * @ORM\Column(name="address_country", type="string", length=10, nullable=true)
     */
    private ?string $addressCountry = null;

    public function getAddress1(): ?string
    {
        return $this->address1;
    }

    public function setAddress1(?string $address1): self
    {
        $this->address1 = $address1;

        return $this;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function setAddress2(?string $address2): self
    {
        $this->address2 = $address2;

        return $this;
    }

    public function getAddress3(): ?string
    {
        return $this->address3;
    }

    public function setAddress3(?string $address3): self
    {
        $this->address3 = $address3;

        return $this;
    }

    public function getAddressPostcode(): ?string
    {
        return $this->addressPostcode;
    }

    public function setAddressPostcode(?string $addressPostcode): self
    {
        $this->addressPostcode = $addressPostcode;

        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry): self
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    public function getAddress4(): ?string
    {
        return $this->address4;
    }

    public function setAddress4(?string $address4): self
    {
        $this->address4 = $address4;

        return $this;
    }

    public function getAddress5(): ?string
    {
        return $this->address5;
    }

    public function setAddress5(?string $address5): self
    {
        $this->address5 = $address5;

        return $this;
    }
}
