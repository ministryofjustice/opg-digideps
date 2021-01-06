<?php

namespace AppBundle\Entity\Traits;

trait AddressTrait
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user", "team", "report-submitted-by"})
     * @ORM\Column(name="address1", type="string", length=200, nullable=true)
     */
    private $address1;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user", "team", "report-submitted-by"})
     * @ORM\Column(name="address2", type="string", length=200, nullable=true)
     */
    private $address2;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user", "team", "report-submitted-by"})
     * @ORM\Column(name="address3", type="string", length=200, nullable=true)
     */
    private $address3;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user", "team", "report-submitted-by"})
     * @ORM\Column(name="address_postcode", type="string", length=10, nullable=true)
     */
    private $addressPostcode;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"user", "team", "report-submitted-by"})
     * @ORM\Column(name="address_country", type="string", length=10, nullable=true)
     */
    private $addressCountry;

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param $address1
     *
     * @return $this
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param $address2
     *
     * @return $this
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * @param $address3
     *
     * @return $this
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressPostcode()
    {
        return $this->addressPostcode;
    }

    /**
     * @param $addressPostcode
     *
     * @return $this
     */
    public function setAddressPostcode($addressPostcode)
    {
        $this->addressPostcode = $addressPostcode;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressCountry()
    {
        return $this->addressCountry;
    }

    /**
     * @param $addressCountry
     *
     * @return $this
     */
    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }
}
