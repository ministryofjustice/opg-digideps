<?php

namespace AppBundle\Entity\Traits;

use AppBundle\Entity\Traits\HasOdrTrait;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

trait OdrActionTrait
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-action-give-gifts"})
     * @ORM\Column(name="action_give_gifts_to_client", type="string", length=3, nullable=true)
     */
    private $actionGiveGiftsToClient;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-action-give-gifts"})
     * @ORM\Column(name="action_give_gifts_to_client_details", type="text", nullable=true)
     */
    private $actionGiveGiftsToClientDetails;


    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-action-property"})
     * @ORM\Column(name="action_property_maintenance", type="string", length=3, nullable=true)
     */
    private $actionPropertyMaintenance;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-action-property"})
     * @ORM\Column(name="action_property_selling_rent", type="string", length=3, nullable=true)
     */
    private $actionPropertySellingRent;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-action-property"})
     * @ORM\Column(name="action_property_buy", type="string", length=3, nullable=true)
     */
    private $actionPropertyBuy;


    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-action-more-info"})
     * @ORM\Column(name="action_more_info", type="string", length=3, nullable=true)
     */
    private $actionMoreInfo;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"odr-action-more-info"})
     * @ORM\Column(name="action_more_info_details", type="text", nullable=true)
     */
    private $actionMoreInfoDetails;

    /**
     * @return string
     */
    public function getActionGiveGiftsToClient()
    {
        return $this->actionGiveGiftsToClient;
    }

    /**
     * @param string $actionGiveGiftsToClient
     * @return OdrActionTrait
     */
    public function setActionGiveGiftsToClient($actionGiveGiftsToClient)
    {
        $this->actionGiveGiftsToClient = $actionGiveGiftsToClient;
        return $this;
    }

    /**
     * @return string
     */
    public function getActionGiveGiftsToClientDetails()
    {
        return $this->actionGiveGiftsToClientDetails;
    }

    /**
     * @param string $actionGiveGiftsToClientDetails
     * @return OdrActionTrait
     */
    public function setActionGiveGiftsToClientDetails($actionGiveGiftsToClientDetails)
    {
        $this->actionGiveGiftsToClientDetails = $actionGiveGiftsToClientDetails;
        return $this;
    }

    /**
     * @return string
     */
    public function getActionPropertyMaintenance()
    {
        return $this->actionPropertyMaintenance;
    }

    /**
     * @param string $actionPropertyMaintenance
     * @return OdrActionTrait
     */
    public function setActionPropertyMaintenance($actionPropertyMaintenance)
    {
        $this->actionPropertyMaintenance = $actionPropertyMaintenance;
        return $this;
    }

    /**
     * @return string
     */
    public function getActionPropertySellingRent()
    {
        return $this->actionPropertySellingRent;
    }

    /**
     * @param string $actionPropertySellingRent
     * @return OdrActionTrait
     */
    public function setActionPropertySellingRent($actionPropertySellingRent)
    {
        $this->actionPropertySellingRent = $actionPropertySellingRent;
        return $this;
    }

    /**
     * @return string
     */
    public function getActionPropertyBuy()
    {
        return $this->actionPropertyBuy;
    }

    /**
     * @param string $actionPropertyBuy
     * @return OdrActionTrait
     */
    public function setActionPropertyBuy($actionPropertyBuy)
    {
        $this->actionPropertyBuy = $actionPropertyBuy;
        return $this;
    }

    /**
     * @return string
     */
    public function getActionMoreInfo()
    {
        return $this->actionMoreInfo;
    }

    /**
     * @param string $actionMoreInfo
     * @return OdrActionTrait
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
     * @return OdrActionTrait
     */
    public function setActionMoreInfoDetails($actionMoreInfoDetails)
    {
        $this->actionMoreInfoDetails = $actionMoreInfoDetails;
        return $this;
    }


}
