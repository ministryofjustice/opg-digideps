<?php

namespace App\Entity\Ndr;

use JMS\Serializer\Annotation as JMS;

class OneOff
{
    /**
     * IncomeBenefit constructor.
     *
     * @param $typeId
     * @param bool   $present
     * @param string $hasMoreDetails
     * @param string $moreDetails
     */
    public function __construct(
        /**
         * @JMS\Type("string")
         * @JMS\Groups({"ndr-one-off"})
         */
        private $typeId,
        /**
         *
         * @JMS\Type("boolean")
         * @JMS\Groups({"ndr-one-off"})
         */
        private $present,
        /**
         * @JMS\Type("boolean")
         */
        private $hasMoreDetails = false,
        /**
         * @JMS\Groups({"ndr-one-off"})
         * @JMS\Type("string")
         */
        private $moreDetails = null
    )
    {
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
