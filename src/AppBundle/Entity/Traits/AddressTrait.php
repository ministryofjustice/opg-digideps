<?php

namespace AppBundle\Entity\Traits;

trait AddressTrait
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\NotBlank( message="user.address1.notBlank", groups={"user_details_full"} )
     * @Assert\Length( max=200, maxMessage="user.address1.maxMessage", groups={"user_details_full"} )
     *
     * @var string
     */
    private $address1;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\Length( max=200, maxMessage="user.address1.maxMessage", groups={"user_details_full"} )
     *
     * @var string
     */
    private $address2;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\Length( max=200, maxMessage="user.address1.maxMessage", groups={"user_details_full"} )
     *
     * @var string
     */
    private $address3;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\NotBlank( message="user.addressPostcode.notBlank", groups={"user_details_full"} )
     * @Assert\Length(min=2, max=10, minMessage="user.addressPostcode.minLength", maxMessage="user.addressPostcode.maxLength", groups={"user_details_full"} )
     *
     * @var string
     */
    private $addressPostcode;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"user_details_full"})
     * @Assert\NotBlank( message="user.addressCountry.notBlank", groups={"user_details_full"} )
     *
     * @var string
     */
    private $addressCountry;

    public function getAddress1()
    {
        return $this->address1;
    }

    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    public function getAddress2()
    {
        return $this->address2;
    }

    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    public function getAddress3()
    {
        return $this->address3;
    }

    public function setAddress3($address3)
    {
        $this->address3 = $address3;
    }

    public function getAddressPostcode()
    {
        return $this->addressPostcode;
    }

    public function setAddressPostcode($addressPostcode)
    {
        $this->addressPostcode = $addressPostcode;
    }

    public function getAddressCountry()
    {
        return $this->addressCountry;
    }

    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }
}