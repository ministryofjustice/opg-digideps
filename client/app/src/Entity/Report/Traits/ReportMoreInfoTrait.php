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
     * @var ?string
     */
    private $actionMoreInfo;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"more-info"})
     * @Assert\NotBlank(message="action.actionMoreInfoDetails.notBlank", groups={"more-info-details"})
     * @var ?string
     */
    private $actionMoreInfoDetails;

    /**
     * @return ?string
     */
    public function getActionMoreInfo()
    {
        return $this->actionMoreInfo;
    }

    /**
     * @param ?string $actionMoreInfo
     */
    public function setActionMoreInfo($actionMoreInfo): self
    {
        $this->actionMoreInfo = $actionMoreInfo;

        return $this;
    }

    /**
     * @return ?string
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
