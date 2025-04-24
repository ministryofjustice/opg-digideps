<?php

namespace App\Entity\Report\Traits;

use App\Entity\Ndr\Ndr;
use JMS\Serializer\Annotation as JMS;

trait MoreInfoTrait
{
    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column(name="action_more_info", type="string", length=3, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['action-more-info'])]
    private $actionMoreInfo;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="action_more_info_details", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['action-more-info'])]
    private $actionMoreInfoDetails;

    /**
     * @return string
     */
    public function getActionMoreInfo()
    {
        return $this->actionMoreInfo;
    }

    /**
     * @param string $actionMoreInfo
     *
     * @return Ndr
     */
    public function setActionMoreInfo($actionMoreInfo)
    {
        $this->actionMoreInfo = $actionMoreInfo;

        return $this;
    }

    /**
     * @return string
     */
    public function getActionMoreInfoDetails()
    {
        return $this->actionMoreInfoDetails;
    }

    /**
     * @param string $actionMoreInfoDetails
     *
     * @return Ndr
     */
    public function setActionMoreInfoDetails($actionMoreInfoDetails)
    {
        $this->actionMoreInfoDetails = $actionMoreInfoDetails;

        return $this;
    }
}
