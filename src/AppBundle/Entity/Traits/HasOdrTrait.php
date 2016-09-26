<?php

namespace AppBundle\Entity\Traits;

use JMS\Serializer\Annotation as JMS;
use AppBundle\Entity\Odr\Odr;

trait HasOdrTrait
{
    /**
     * @JMS\Type("AppBundle\Entity\Odr\Odr")
     * @JMS\Groups({"odr-id"})
     */
    private $odr;

    /**
     * @JMS\VirtualProperty
     * @JMS\Groups({"odr-id"})
     *
     * @return int
     */
    public function getOdrId()
    {
        return $this->odr ? $this->odr->getId() : null;
    }

    /**
     * @return Odr
     */
    public function getOdr()
    {
        return $this->odr;
    }

    /**
     * @param Odr $odr
     */
    public function setOdr($odr)
    {
        $this->odr = $odr;

        return $this;
    }
}
