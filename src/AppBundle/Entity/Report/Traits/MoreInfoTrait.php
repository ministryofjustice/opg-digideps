<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Odr\Odr;
use AppBundle\Entity\Odr\Traits\HasOdrTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait MoreInfoTrait
{

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"action-more-info"})
     * @ORM\Column(name="action_more_info", type="string", length=3, nullable=true)
     */
    private $actionMoreInfo;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"action-more-info"})
     * @ORM\Column(name="action_more_info_details", type="text", nullable=true)
     */
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
     * @return Odr
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
     * @return Odr
     */
    public function setActionMoreInfoDetails($actionMoreInfoDetails)
    {
        $this->actionMoreInfoDetails = $actionMoreInfoDetails;

        return $this;
    }

}
