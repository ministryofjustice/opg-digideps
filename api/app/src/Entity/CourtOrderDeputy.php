<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Link table between court orders and deputies.
 *
 * @ORM\Table(name="court_order_deputy")
 *
 * @ORM\Entity(repositoryClass="App\Repository\CourtOrderDeputyRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class CourtOrderDeputy
{
    /**
     * @ORM\Id
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CourtOrder", inversedBy="courtOrderDeputyRelationships", cascade={"persist"})
     *
     * @ORM\JoinColumn(name="court_order_id", referencedColumnName="id", nullable=false)
     */
    private CourtOrder $courtOrder;

    /**
     * @JMS\Groups({"deputy"})
     *
     * @ORM\Id
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Deputy", cascade={"persist"})
     *
     * @ORM\JoinColumn(name="deputy_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private Deputy $deputy;

    /**
     * @ORM\Column(name="is_active", type="boolean", nullable=false)
     */
    private bool $isActive;

    public function __construct()
    {
        $this->isActive = true;
    }

    public function getDeputy(): Deputy
    {
        return $this->deputy;
    }

    public function setDeputy(Deputy $deputy): CourtOrderDeputy
    {
        $this->deputy = $deputy;

        return $this;
    }

    public function getCourtOrder(): CourtOrder
    {
        return $this->courtOrder;
    }

    public function setCourtOrder(CourtOrder $courtOrder): CourtOrderDeputy
    {
        $this->courtOrder = $courtOrder;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): CourtOrderDeputy
    {
        $this->isActive = $isActive;

        return $this;
    }
}
