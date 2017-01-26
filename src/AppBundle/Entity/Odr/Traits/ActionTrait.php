<?php

namespace AppBundle\Entity\Odr\Traits;

use AppBundle\Entity\Odr\Odr;
use AppBundle\Entity\Odr\Traits\HasOdrTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait ActionTrait
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
     * @return string
     */
    public function getActionGiveGiftsToClient()
    {
        return $this->actionGiveGiftsToClient;
    }

    /**
     * @param string $actionGiveGiftsToClient
     * @return Odr
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
     * @return Odr
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
     * @return Odr
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
     * @return Odr
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
     * @return Odr
     */
    public function setActionPropertyBuy($actionPropertyBuy)
    {
        $this->actionPropertyBuy = $actionPropertyBuy;

        return $this;
    }
}
