<?php

namespace AppBundle\Entity\Traits;

use JMS\Serializer\Annotation as JMS;

trait AddressTrait
{
    /**
     * @JMS\Type("string")
     * @var string
     */
    private $address;

    /**
     * @JMS\Type("string")
     * @var string
     */
    private $address2;

    /**
     * @JMS\Type("string")
     * @var string
     */
    private $county;

     /**
     * @JMS\Type("string")
     * @var string $country
     */
    //private $country;
    
    /**
     * @JMS\Type("string")
     * @var string
     */
    private $postcode;

    /**
     * Set address
     *
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set postcode
     *
     * @param string $postcode
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get address2
     *
     * @return string 
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set county
     *
     * @param string $county
     */
    public function setCounty($county)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Get county
     *
     * @return string 
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Get postcode
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Set address2
     *
     * @param string $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }
    
    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }



}
