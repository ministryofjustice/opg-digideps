<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use OPG\Digideps\Frontend\Entity\Report\Gift;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportGiftTrait
{
    /**
     * @var string
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['gifts-exist'])]
    #[Assert\NotBlank(message: 'gifts.giftsExist.notBlank', groups: ['gifts-exist'])]
    private $giftsExist;

    /**
     *
     * @var Gift[]
     */
    #[JMS\Type('array<OPG\Digideps\Frontend\Entity\Report\Gift>')]
    #[JMS\Groups(['gifts'])]
    private array $gifts = [];

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
    public function setGiftsExist($giftsExist): static
    {
        $this->giftsExist = $giftsExist;

        return $this;
    }

    /**
     * @return Gift[]
     */
    public function getGifts(): array
    {
        return $this->gifts;
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
