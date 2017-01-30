<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Gift;
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
     * @JMS\Type("array<AppBundle\Entity\Report\Gift>")
     * @JMS\Groups({"gifts"})
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
     * @return \AppBundle\Entity\Report\Gift[]
     */
    public function getGifts()
    {
        return $this->gifts;
    }

    /**
     * @param \AppBundle\Entity\Report\Gift[] $gifts
     */
    public function setGifts($gifts)
    {
        $this->gifts = $gifts;
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
