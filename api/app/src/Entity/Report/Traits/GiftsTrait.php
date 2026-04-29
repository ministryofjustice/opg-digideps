<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Backend\Entity\Report\Gift;
use OPG\Digideps\Backend\Entity\Report\Report;

trait GiftsTrait
{
    /**
     * @var ?string yes|no|null
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['gifts'])]
    #[ORM\Column(name: 'gifts_exist', type: 'string', length: 3, nullable: true)]
    private $giftsExist;

    /**
     * @var ?Collection<int, Gift>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Gift>')]
    #[JMS\Groups(['gifts'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Gift::class, cascade: ['persist', 'remove'])]
    private $gifts;

    /**
     * @return ?string
     */
    public function getGiftsExist()
    {
        return $this->giftsExist;
    }

    /**
     * @param string $giftsExist
     */
    public function setGiftsExist($giftsExist)
    {
        $this->giftsExist = $giftsExist;
    }

    /**
     * @return Collection<int, Gift>
     */
    public function getGifts()
    {
        return $this->gifts ?? new ArrayCollection();
    }

    /**
     * @param ?Collection<int, Gift> $gifts
     */
    public function setGifts($gifts): static
    {
        $this->gifts = $gifts;

        return $this;
    }

    /**
     * //TODO unit test.
     *
     * @return bool
     */
    public function giftsSectionCompleted()
    {
        return count($this->getGifts()) > 0 || 'no' === $this->getGiftsExist();
    }

    public function addGift(Gift $gift): static
    {
        if ($this->gifts !== null && !$this->gifts->contains($gift)) {
            $this->gifts->add($gift);
        }

        return $this;
    }

    /**
     * @return float
     */
    protected function getGiftsTotal()
    {
        $ret = 0;
        foreach ($this->getGifts() as $record) {
            $ret += (float) $record->getAmount();
        }

        return $ret;
    }
}
