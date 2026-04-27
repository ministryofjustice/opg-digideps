<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportMoreInfoTrait
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"more-info"})
     * @Assert\NotBlank(message="action.actionMoreInfo.notBlank", groups={"more-info"})
     */
    private $actionMoreInfo;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"more-info"})
     * @Assert\NotBlank(message="action.actionMoreInfoDetails.notBlank", groups={"more-info-details"})
     */
    private $actionMoreInfoDetails;

    /**
     * @return mixed
     */
    public function getActionMoreInfo()
    {
        return $this->actionMoreInfo;
    }

    /**
     * @param mixed $actionMoreInfo
     */
    public function setActionMoreInfo($actionMoreInfo): self
    {
        $this->actionMoreInfo = $actionMoreInfo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActionMoreInfoDetails()
    {
        return $this->actionMoreInfoDetails;
    }

    /**
     * @param mixed $actionMoreInfoDetails
     */
    public function setActionMoreInfoDetails($actionMoreInfoDetails): self
    {
        $this->actionMoreInfoDetails = $actionMoreInfoDetails;

        return $this;
    }
}
