<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Link table between court orders and deputies.
 *
 * @ORM\Table(name="court_order_deputy")
 *
 * @ORM\Entity()
 *
 * @ORM\HasLifecycleCallbacks()
 */
class CourtOrderDeputy
{
    /**
     * @ORM\Id
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CourtOrder")
     *
     * @ORM\JoinColumn(name="court_order_id", referencedColumnName="id", nullable=false)
     */
    private $courtOrder;

    /**
     * @ORM\Id
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Deputy")
     *
     * @ORM\JoinColumn(name="deputy_id", referencedColumnName="id", nullable=false)
     */
    private $deputy;

    /**
     * @ORM\Column(name="discharged", type="boolean", nullable=false)
     */
    private bool $discharged;

    public function getDeputy(): Deputy
    {
        return $this->deputy;
    }

    public function getCourtOrder(): CourtOrder
    {
        return $this->courtOrder;
    }

    public function isDischarged(): bool
    {
        return $this->discharged;
    }

    public function setDischarged(bool $discharged)
    {
        $this->discharged = $discharged;

        return $this;
    }
}
