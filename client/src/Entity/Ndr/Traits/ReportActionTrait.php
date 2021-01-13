<?php

namespace App\Entity\Ndr\Traits;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ReportActionTrait
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action"})
     * @Assert\NotBlank(message="ndr.action.actionGiveGiftsToClient.notBlank", groups={"action-give-gifts"})
     */
    private $actionGiveGiftsToClient;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action"})
     * @Assert\NotBlank(message="ndr.action.actionGiveGiftsToClientDetails.notBlank", groups={"action-give-gifts-details"})
     */
    private $actionGiveGiftsToClientDetails;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action"})
     * @Assert\NotBlank(message="ndr.action.actionPropertyMaintenance.notBlank", groups={"action-property-maintenance"})
     */
    private $actionPropertyMaintenance;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action"})
     * @Assert\NotBlank(message="ndr.action.actionPropertySellingRent.notBlank", groups={"action-property-selling-rent"})
     */
    private $actionPropertySellingRent;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"action"})
     * @Assert\NotBlank(message="ndr.action.actionPropertyBuy.notBlank", groups={"action-property-buy"})
     */
    private $actionPropertyBuy;

    public function hasAtLeastOneAction()
    {
        return !empty($this->actionGiveGiftsToClient)
        || !empty($this->actionPropertyMaintenance)
        || !empty($this->actionPropertySellingRent)
        || !empty($this->actionPropertyBuy);
    }

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
}
