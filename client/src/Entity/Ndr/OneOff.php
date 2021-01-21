<?php

namespace App\Entity\Ndr;

use JMS\Serializer\Annotation as JMS;
use App\Validator\Constraints as AppAssert;

class OneOff
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"ndr-one-off"})
     */
    private $typeId;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"ndr-one-off"})
     */
    private $present;

    /**
     * @var string
     * @JMS\Type("boolean")
     * @AppAssert\TextNoSpecialCharacters
     */
    private $hasMoreDetails;

    /**
     * @var string
     * @JMS\Groups({"ndr-one-off"})
     * @JMS\Type("string")
     * @AppAssert\TextNoSpecialCharacters
     */
    private $moreDetails;

    /**
     * IncomeBenefit constructor.
     *
     * @param $typeId
     * @param bool   $present
     * @param string $hasMoreDetails
     * @param string $moreDetails
     */
    public function __construct($typeId, $present, $hasMoreDetails = false, $moreDetails = null)
    {
        $this->typeId = $typeId;
        $this->present = $present;
        $this->hasMoreDetails = $hasMoreDetails;
        $this->moreDetails = $moreDetails;
    }

    /**
     * @return mixed
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param mixed $typeId
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
    }

    /**
     * @return bool
     */
    public function isPresent()
    {
        return $this->present;
    }

    /**
     * @param bool $present
     */
    public function setPresent($present)
    {
        $this->present = $present;
    }

    /**
     * @return string
     */
    public function getHasMoreDetails()
    {
        return $this->hasMoreDetails;
    }

    /**
     * @param string $hasMoreDetails
     */
    public function setHasMoreDetails($hasMoreDetails)
    {
        $this->hasMoreDetails = $hasMoreDetails;
    }

    /**
     * @return string
     */
    public function getMoreDetails()
    {
        return $this->moreDetails;
    }

    /**
     * @param string $moreDetails
     */
    public function setMoreDetails($moreDetails)
    {
        $this->moreDetails = $moreDetails;
    }
}
