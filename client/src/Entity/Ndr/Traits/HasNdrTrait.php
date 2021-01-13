<?php

namespace App\Entity\Ndr\Traits;

use App\Entity\Ndr\Ndr;
use JMS\Serializer\Annotation as JMS;

trait HasNdrTrait
{
    /**
     * @JMS\Type("App\Entity\Ndr\Ndr")
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
