<?php

namespace AppBundle\Entity\Ndr\Traits;

use AppBundle\Entity\Ndr\Ndr;
use JMS\Serializer\Annotation as JMS;

trait HasNdrTrait
{
    /**
     * @JMS\Type("AppBundle\Entity\Ndr\Ndr")
     * @JMS\Groups({"ndr-id"})
     */
    private $ndr;

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"ndr-id"})
     *
     * @return int
     */
    public function getNdrId()
    {
        return $this->ndr ? $this->ndr->getId() : null;
    }

    /**
     * @return Ndr
     */
    public function getNdr()
    {
        return $this->ndr;
    }

    /**
     * @param Ndr $ndr
     */
    public function setNdr($ndr)
    {
        $this->ndr = $ndr;

        return $this;
    }
}
