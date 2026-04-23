<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

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
     * @var Collection<Gift>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Gift>')]
    #[JMS\Groups(['gifts'])]
    #[ORM\OneToMany(mappedBy: 'report', targetEntity: Gift::class, cascade: ['persist', 'remove'])]
    private $gifts;

    /**
     * @return string
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
     * @return Collection<Gift>
     */
    public function getGifts()
    {
        return $this->gifts;
    }

    /**
     * @param ?Collection<Gift> $gifts
     *
     * @return Report
     */
    public function setGifts($gifts)
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

    /**
     * @return Report
     */
    public function addGift(Gift $gift)
    {
        if (!$this->gifts->contains($gift)) {
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
            $ret += $record->getAmount();
        }

        return $ret;
    }
}
