<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;

class MoneyShortCategory
{
    #[JMS\Type('string')]
    #[JMS\Groups(['moneyShortCategoriesIn', 'moneyShortCategoriesOut'])]
    private string $typeId;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['moneyShortCategoriesIn', 'moneyShortCategoriesOut'])]
    private bool $present;

    public function __construct(string $typeId, bool $present)
    {
        $this->typeId = $typeId;
        $this->present = $present;
    }

    public function getTypeId(): string
    {
        return $this->typeId;
    }

    public function setTypeId(string $typeId): static
    {
        $this->typeId = $typeId;

        return $this;
    }

    public function isPresent(): bool
    {
        return $this->present;
    }

    public function setPresent(bool $present): static
    {
        $this->present = $present;

        return $this;
    }
}
