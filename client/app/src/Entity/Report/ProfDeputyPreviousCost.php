<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Validator\Constraints\EndDateNotBeforeStartDate;
use OPG\Digideps\Frontend\Validator\Constraints\EndDateNotGreaterThanFifteenMonths;
use OPG\Digideps\Frontend\Validator\Constraints\StartEndDateComparableInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[EndDateNotBeforeStartDate(groups: ['prof-deputy-prev-costs'])]
#[EndDateNotGreaterThanFifteenMonths(groups: ['prof-deputy-prev-costs'])]
class ProfDeputyPreviousCost implements StartEndDateComparableInterface
{
    #[JMS\Type('integer')]
    private ?int $id = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['profDeputyPrevCosts'])]
    #[Assert\NotBlank(message: 'profDeputyPreviousCost.startDate.notBlank', groups: ['prof-deputy-prev-costs'])]
    #[Assert\Type(type: 'DateTimeInterface', message: 'profDeputyPreviousCost.startDate.notValid', groups: ['prof-deputy-prev-costs'])]
    private ?\DateTime $startDate = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['profDeputyPrevCosts'])]
    #[Assert\NotBlank(message: 'profDeputyPreviousCost.endDate.notBlank', groups: ['prof-deputy-prev-costs'])]
    #[Assert\Type(type: 'DateTimeInterface', message: 'profDeputyPreviousCost.endDate.notValid', groups: ['prof-deputy-prev-costs'])]
    private ?\DateTime $endDate = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['profDeputyPrevCosts'])]
    #[Assert\NotBlank(message: 'profDeputyPreviousCost.amount.notBlank', groups: ['prof-deputy-prev-costs'])]
    #[Assert\Range(notInRangeMessage: 'profDeputyPreviousCost.amount.notInRangeMessage', min: 0.01, max: 10000000, groups: ['prof-deputy-prev-costs'])]
    private ?string $amount = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }
}
