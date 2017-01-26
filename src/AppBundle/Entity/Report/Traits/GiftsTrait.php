<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Gift;
use AppBundle\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait GiftsTrait
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"gifts"})
     * @ORM\Column(name="gifts_exist", type="string", length=3, nullable=true)
     */
    private $giftsExist;

    /**
     * @JMS\Type("array<AppBundle\Entity\Report\Gift>")
     * @JMS\Groups({"gifts"})
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Gift", mappedBy="report", cascade={"persist"})
     *
     * @var Gift[]
     */
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
     * @param Gift $gift
     *
     * @return Report
     */
    public function addGift(Gift $gift)
    {
        if (!$this->gifts->contains($gift)) {
            $this->gifts->add($gift);
        }

        return $this;
    }
}
