<?php

namespace App\Entity\Report;

use JMS\Serializer\Annotation as JMS;

class MoneyShortCategory
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"moneyShortCategoriesIn", "moneyShortCategoriesOut"})
     */
    private $typeId;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"moneyShortCategoriesIn", "moneyShortCategoriesOut"})
     */
    private $present;

    /**
     * IncomeBenefit constructor.
     *
     * @param $typeId
     * @param bool $present
     */
    public function __construct($typeId, $present)
    {
        $this->typeId = $typeId;
        $this->present = $present;
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
}
