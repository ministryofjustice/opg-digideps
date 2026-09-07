<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\Gift;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportGiftTrait
{
    /**
     * @var ?string 'yes'|'no'|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['gifts-exist'])]
    #[Assert\NotBlank(message: 'gifts.giftsExist.notBlank', groups: ['gifts-exist'])]
    private ?string $giftsExist = null;

    /**
     * @var Gift[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Gift>')]
    #[JMS\Groups(['gifts'])]
    private array $gifts = [];

    public function getGiftsExist(): ?string
    {
        return $this->giftsExist;
    }

    public function setGiftsExist(?string $giftsExist): static
    {
        $this->giftsExist = $giftsExist;

        return $this;
    }

    /**
     * Return gifts ordered by createdAt in ascending order.
     * Does not change the order of the underling $this->gifts property.
     *
     * @return Gift[]
     */
    public function getGifts(): array
    {
        $gifts = [...$this->gifts];
        uasort($gifts, fn ($gift1, $gift2) => $gift1 <=> $gift2);
        return $gifts;
    }

    /**
     * @param Gift[] $gifts
     */
    public function setGifts(array $gifts): static
    {
        $this->gifts = $gifts;

        return $this;
    }

    /**
     * Get gifts total value.
     */
    public function getGiftsTotalValue(): float
    {
        $ret = 0.0;
        foreach ($this->getGifts() as $gift) {
            $ret += $gift->getAmount();
        }

        return $ret;
    }
}
