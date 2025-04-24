<?php

namespace App\Entity\Ndr\Traits;

use App\Entity\Ndr\Ndr;
use JMS\Serializer\Annotation as JMS;

trait ActionTrait
{
    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column(name="action_give_gifts_to_client", type="string", length=3, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['ndr-action-give-gifts'])]
    private $actionGiveGiftsToClient;

    /**
     * @var string
     *
     *
     *
     * @ORM\Column(name="action_give_gifts_to_client_details", type="text", nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['ndr-action-give-gifts'])]
    private $actionGiveGiftsToClientDetails;

    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column(name="action_property_maintenance", type="string", length=3, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['ndr-action-property'])]
    private $actionPropertyMaintenance;

    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column(name="action_property_selling_rent", type="string", length=3, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['ndr-action-property'])]
    private $actionPropertySellingRent;

    /**
     * @var string yes|no|null
     *
     *
     *
     * @ORM\Column(name="action_property_buy", type="string", length=3, nullable=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['ndr-action-property'])]
    private $actionPropertyBuy;

    /**
     * @return string
     */
    public function getActionGiveGiftsToClient()
    {
        return $this->actionGiveGiftsToClient;
    }

    /**
     * @param string $actionGiveGiftsToClient
     *
     * @return Ndr
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
     *
     * @return Ndr
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
     *
     * @return Ndr
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
     *
     * @return Ndr
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
     *
     * @return Ndr
     */
    public function setActionPropertyBuy($actionPropertyBuy)
    {
        $this->actionPropertyBuy = $actionPropertyBuy;

        return $this;
    }
}
