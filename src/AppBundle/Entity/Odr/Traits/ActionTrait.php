<?php

namespace AppBundle\Entity\Odr\Traits;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

trait ActionTrait
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action-give-gifts"})
     * @Assert\NotBlank(message="odr.action.actionGiveGiftsToClient.notBlank", groups={"action-give-gifts"})
     */
    private $actionGiveGiftsToClient;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action-give-gifts"})
     * @Assert\NotBlank(message="odr.action.actionGiveGiftsToClientDetails.notBlank", groups={"action-give-gifts-details"})
     */
    private $actionGiveGiftsToClientDetails;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action-property"})
     * @Assert\NotBlank(message="odr.action.actionPropertyMaintenance.notBlank", groups={"action-property"})
     */
    private $actionPropertyMaintenance;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action-property"})
     * @Assert\NotBlank(message="odr.action.actionPropertySellingRent.notBlank", groups={"action-property"})
     */
    private $actionPropertySellingRent;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action-property"})
     * @Assert\NotBlank(message="odr.action.actionPropertyBuy.notBlank", groups={"action-property"})
     */
    private $actionPropertyBuy;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action-more-info"})
     * @Assert\NotBlank(message="odr.action.actionMoreInfo.notBlank", groups={"action-more-info"})
     */
    private $actionMoreInfo;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action-more-info"})
     * @Assert\NotBlank(message="odr.action.actionMoreInfoDetails.notBlank", groups={"action-more-info-details"})
     */
    private $actionMoreInfoDetails;

    /**
     * @return mixed
     */
    public function getActionGiveGiftsToClient()
    {
        return $this->actionGiveGiftsToClient;
    }

    /**
     * @param mixed $actionGiveGiftsToClient
     *
     * @return ActionTrait
     */
    public function setActionGiveGiftsToClient($actionGiveGiftsToClient)
    {
        $this->actionGiveGiftsToClient = $actionGiveGiftsToClient;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActionGiveGiftsToClientDetails()
    {
        return $this->actionGiveGiftsToClientDetails;
    }

    /**
     * @param mixed $actionGiveGiftsToClientDetails
     *
     * @return ActionTrait
     */
    public function setActionGiveGiftsToClientDetails($actionGiveGiftsToClientDetails)
    {
        $this->actionGiveGiftsToClientDetails = $actionGiveGiftsToClientDetails;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActionPropertyMaintenance()
    {
        return $this->actionPropertyMaintenance;
    }

    /**
     * @param mixed $actionPropertyMaintenance
     *
     * @return ActionTrait
     */
    public function setActionPropertyMaintenance($actionPropertyMaintenance)
    {
        $this->actionPropertyMaintenance = $actionPropertyMaintenance;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActionPropertySellingRent()
    {
        return $this->actionPropertySellingRent;
    }

    /**
     * @param mixed $actionPropertySellingRent
     *
     * @return ActionTrait
     */
    public function setActionPropertySellingRent($actionPropertySellingRent)
    {
        $this->actionPropertySellingRent = $actionPropertySellingRent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActionPropertyBuy()
    {
        return $this->actionPropertyBuy;
    }

    /**
     * @param mixed $actionPropertyBuy
     *
     * @return ActionTrait
     */
    public function setActionPropertyBuy($actionPropertyBuy)
    {
        $this->actionPropertyBuy = $actionPropertyBuy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getActionMoreInfo()
    {
        return $this->actionMoreInfo;
    }

    /**
     * @param mixed $actionMoreInfo
     *
     * @return ActionTrait
     */
    public function setActionMoreInfo($actionMoreInfo)
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
     *
     * @return ActionTrait
     */
    public function setActionMoreInfoDetails($actionMoreInfoDetails)
    {
        $this->actionMoreInfoDetails = $actionMoreInfoDetails;

        return $this;
    }
}
