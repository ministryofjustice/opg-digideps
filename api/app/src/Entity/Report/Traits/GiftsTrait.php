<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\Gift;
use App\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait GiftsTrait
{
    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column(name="gifts_exist", type="string", length=3, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['gifts'])]
    private $giftsExist;

    /**
     * @var Gift[]
     *
     *
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Report\Gift", mappedBy="report", cascade={"persist", "remove"})
     */
    #[JMS\Type('ArrayCollection<App\Entity\Report\Gift>')]
    #[JMS\Groups(['gifts'])]
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
     * @return Gift[]
     */
    public function getGifts()
    {
        return $this->gifts;
    }

    /**
     * @param Gift[]|null $gifts
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
