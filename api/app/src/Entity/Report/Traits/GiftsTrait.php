<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Gift;

trait GiftsTrait
{
    /**
     * yes|no|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['gifts'])]
    #[ORM\Column(name: 'gifts_exist', type: 'string', length: 3, nullable: true)]
    private ?string $giftsExist = null;

    /**
     * @var Collection<int, Gift>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Gift>')]
    #[JMS\Groups(['gifts'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Gift::class, cascade: ['persist', 'remove'])]
    private Collection $gifts;

    public function getGiftsExist(): ?string
    {
        return $this->giftsExist;
    }

    public function setGiftsExist(?string $giftsExist): void
    {
        $this->giftsExist = $giftsExist;
    }

    /**
     * @return Collection<int, Gift>
     */
    public function getGifts(): Collection
    {
        return $this->gifts;
    }

    /**
     * @param Collection<int, Gift> $gifts
     */
    public function setGifts(Collection $gifts): static
    {
        $this->gifts = $gifts;

        return $this;
    }

    public function giftsSectionCompleted(): bool
    {
        return count($this->getGifts()) > 0 || $this->getGiftsExist() === 'no';
    }

    public function addGift(Gift $gift): static
    {
        if (!$this->gifts->contains($gift)) {
            $this->gifts->add($gift);
        }

        return $this;
    }

    protected function getGiftsTotal(): float
    {
        $ret = 0.0;
        foreach ($this->getGifts() as $record) {
            $ret += (float) $record->getAmount();
        }

        return $ret;
    }
}
