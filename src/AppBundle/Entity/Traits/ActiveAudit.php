<?php

namespace AppBundle\Entity\Traits;

/**
 * ActiveAudit Trait, usable with PHP >= 5.4
 *
 */
trait ActiveAudit
{
    /**
     * Get date that this entity was active from
     *
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"active-period"})
     */
    private $activeFrom;

    /**
     * Get date that this entity was active to
     *
     * @var \DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     * @JMS\Groups({"active-period"})
     */
    private $activeTo;

    /**
     * @return \DateTime
     */
    public function getActiveFrom()
    {
        return $this->activeFrom;
    }

    /**
     * @return \DateTime
     */
    public function getActiveTo()
    {
        return $this->activeTo;
    }
}
