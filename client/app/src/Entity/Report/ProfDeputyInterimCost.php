<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class ProfDeputyInterimCost
{
    #[JMS\Type('integer')]
    private ?int $id = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['profDeputyInterimCosts'])]
    #[Assert\Range(notInRangeMessage: 'profDeputyInterimCost.amount.notInRangeMessage', min: 0.01, max: 10000000, groups: ['prof-deputy-interim-costs'])]
    private ?string $amount = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['profDeputyInterimCosts'])]
    #[Assert\Type(type: 'DateTimeInterface', message: 'profDeputyInterimCost.date.notValid', groups: ['prof-deputy-interim-costs'])]
    #[Assert\LessThanOrEqual('today', message: 'profDeputyInterimCost.date.notFuture', groups: ['prof-deputy-interim-costs'])]
    private ?\DateTime $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

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

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(?\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }
}
