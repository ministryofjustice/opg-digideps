<?php

namespace App\Entity;

use JMS\Serializer\Annotation as JMS;

/**
 * Deputy.
 */
class Deputy implements DeputyInterface
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $deputyUid;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $firstname;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $lastname;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $email1;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $email2;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $email3;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $address1;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $address2;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $address3;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $address4;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $address5;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $addressPostcode;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $addressCountry;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $phoneMain;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $phoneAlternative;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getDeputyUid(): string
    {
        return $this->deputyUid;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     *
     * @return $this
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     *
     * @return $this
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstname.' '.$this->lastname;
    }

    /**
     * @return string
     * @return $this
     */
    public function getEmail1()
    {
        return $this->email1;
    }

    /**
     * @return string
     * @return $this
     */
    public function getEmail()
    {
        return $this->email1;
    }

    /**
     * @param string $email1
     *
     * @return $this
     */
    public function setEmail1($email1)
    {
        $this->email1 = $email1;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail2()
    {
        return $this->email2;
    }

    /**
     * @param string $email2
     *
     * @return $this
     */
    public function setEmail2($email2)
    {
        $this->email2 = $email2;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail3()
    {
        return $this->email3;
    }

    /**
     * @param string $email3
     *
     * @return $this
     */
    public function setEmail3($email3)
    {
        $this->email3 = $email3;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address1
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
     * @param string $address2
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
     * @param string $address3
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
    public function getAddress4()
    {
        return $this->address4;
    }

    /**
     * @param string $address4
     *
     * @return $this
     */
    public function setAddress4($address4)
    {
        $this->address4 = $address4;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress5()
    {
        return $this->address5;
    }

    /**
     * @param string $address5
     *
     * @return $this
     */
    public function setAddress5($address5)
    {
        $this->address5 = $address5;

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
     * @param string $addressPostcode
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
     * @param string $addressCountry
     *
     * @return $this
     */
    public function setAddressCountry($addressCountry)
    {
        $this->addressCountry = $addressCountry;

        return $this;
    }

    /**
     * @return array
     */
    public function getAddressNotEmptyParts()
    {
        return array_filter([
            $this->address1,
            $this->address2,
            $this->address3,
            $this->address4,
            $this->address5,
            $this->addressPostcode,
            $this->addressCountry,
        ]);
    }

    /**
     * @return string
     */
    public function getPhoneMain()
    {
        return $this->phoneMain;
    }

    /**
     * @param string $phoneMain
     *
     * @return $this
     */
    public function setPhoneMain($phoneMain)
    {
        $this->phoneMain = trim($phoneMain);

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneAlternative()
    {
        return $this->phoneAlternative;
    }

    /**
     * @param string $phoneAlternative
     *
     * @return $this
     */
    public function setPhoneAlternative($phoneAlternative)
    {
        $this->phoneAlternative = trim($phoneAlternative);

        return $this;
    }
}
