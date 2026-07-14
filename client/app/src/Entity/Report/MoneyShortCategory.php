<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;

class MoneyShortCategory
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"moneyShortCategoriesIn", "moneyShortCategoriesOut"})
     */
    private string $typeId;

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
     * @param string $typeId
     * @param bool $present
     */
    public function __construct(string $typeId, $present)
    {
        $this->typeId = $typeId;
        $this->present = $present;
    }

    public function getTypeId(): string
    {
        return $this->typeId;
    }

    public function setTypeId(string $typeId): void
    {
        $this->typeId = $typeId;
    }

    /**
     * @return bool
     */
    public function isPresent(): bool
    {
        return $this->present;
    }

    public function setPresent(bool $present): void
    {
        $this->present = $present;
    }
}
