<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\Gift;
use App\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportGiftTrait
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"gifts-exist"})
     * @Assert\NotBlank(message="gifts.giftsExist.notBlank", groups={"gifts-exist"})
     */
    private $giftsExist;

    /**
     * @JMS\Type("array<App\Entity\Report\Gift>")
     * @JMS\Groups({"gifts"})
     *
     * @var Gift[]
     */
    private $gifts = [];

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
     * @return \App\Entity\Report\Gift[]
     */
    public function getGifts()
    {
        return $this->gifts;
    }

    /**
     * @param array $gifts
     *
     * @return Report
     */
    public function setGifts($gifts)
    {
        $this->gifts = $gifts;

        return $this;
    }

    /**
     * Get gifts total value.
     *
     * @return float
     */
    public function getGiftsTotalValue()
    {
        $ret = 0;
        foreach ($this->getGifts() as $gift) {
            $ret += $gift->getAmount();
        }

        return $ret;
    }
}
