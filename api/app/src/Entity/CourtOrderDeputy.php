<?php

namespace App\Entity;

use App\Repository\CourtOrderDeputyRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Link table between court orders and deputies.
 */
#[ORM\Table(name: 'court_order_deputy')]
#[ORM\Entity(repositoryClass: CourtOrderDeputyRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CourtOrderDeputy
{

    #[ORM\JoinColumn(name: 'court_order_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: CourtOrder::class, inversedBy: 'courtOrderDeputyRelationships', cascade: ['persist'])]
    private CourtOrder $courtOrder;

    /**
     * @JMS\Groups({"deputy"})
     */
    #[ORM\JoinColumn(name: 'deputy_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Deputy::class, cascade: ['persist'])]
    private Deputy $deputy;

    #[ORM\Column(name: 'is_active', type: 'boolean', nullable: false)]
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
