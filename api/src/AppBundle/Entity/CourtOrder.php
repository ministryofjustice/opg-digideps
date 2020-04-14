<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Report\Report;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Court Order
 *
 * @ORM\Table(name="court_order")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\CourtOrderRepository")
 */
class CourtOrder
{
    const SUBTYPE_HW = 'HW';
    const SUBTYPE_PFA = 'PFA';

    const LEVEL_MINIMAL = 'MINIMAL';
    const LEVEL_GENERAL = 'GENERAL';

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="court_order_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     * @Assert\Choice({CourtOrder::SUBTYPE_HW, CourtOrder::SUBTYPE_PFA})
     * @ORM\Column(name="type", type="string", nullable=false, length=4)
     */
    private $type;

    /**
     * @var string|null
     * @Assert\Choice({CourtOrder::LEVEL_MINIMAL, CourtOrder::LEVEL_GENERAL})
     * @ORM\Column(name="supervision_level", type="string", nullable=true, length=8)
     */
    private $supervisionLevel;

    /**
     * @var DateTime
     * @ORM\Column(name="order_date", type="date", nullable=false)
     */
    private $orderDate;

    /**
     * @var string
     * @ORM\Column(name="case_number", type="string", length=16, nullable=false)
     */
    private $caseNumber;

    /**
     * @var Client
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Client", inversedBy="courtOrders", cascade={"persist"})
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $client;

    /**
     * @var Collection<int, Report>
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report\Report", mappedBy="courtOrder")
     */
    private $reports;

    /**
     * @var Collection<int, CourtOrderDeputy>
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CourtOrderDeputy", mappedBy="courtOrder", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $deputies;

    public function __construct()
    {
        $this->reports = new ArrayCollection();
        $this->deputies = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getSupervisionLevel(): ?string
    {
        return $this->supervisionLevel;
    }

    /**
     * @return DateTime
     */
    public function getOrderDate(): DateTime
    {
        return $this->orderDate;
    }

    /**
     * @return string
     */
    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): iterable
    {
        return $this->reports;
    }

    /**
     * @return Collection<int, CourtOrderDeputy>
     */
    public function getDeputies(): Collection
    {
        return $this->deputies;
    }

    /**
     * @param string $type
     * @return CourtOrder
     */
    public function setType(string $type): CourtOrder
    {
        if (!in_array($type, [self::SUBTYPE_HW, self::SUBTYPE_PFA])) {
            throw new InvalidArgumentException('Invalid CourtOrder type');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * @param string|null $supervisionLevel
     * @return CourtOrder
     */
    public function setSupervisionLevel(?string $supervisionLevel): CourtOrder
    {
        if (null !== $supervisionLevel && !in_array($supervisionLevel, [self::LEVEL_GENERAL, self::LEVEL_MINIMAL])) {
            throw new InvalidArgumentException("Invalid CourtOrder supervision level: $supervisionLevel");
        }

        $this->supervisionLevel = $supervisionLevel;

        return $this;
    }

    /**
     * @param DateTime $date
     * @return CourtOrder
     */
    public function setOrderDate(DateTime $date): CourtOrder
    {
        $this->orderDate = $date;

        return $this;
    }

    /**
     * @param string $caseNumber
     * @return $this
     */
    public function setCaseNumber(string $caseNumber): CourtOrder
    {
        $this->caseNumber = $caseNumber;

        return $this;
    }

    /**
     * @param Client $client
     * @return CourtOrder
     */
    public function setClient(Client $client): CourtOrder
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @param Report $report
     * @return CourtOrder
     */
    public function addReport(Report $report): CourtOrder
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
        }

        return $this;
    }

    /**
     * @param CourtOrderDeputy $deputy
     * @return CourtOrder
     */
    public function addDeputy(CourtOrderDeputy $deputy): CourtOrder
    {
        if (!$this->deputies->contains($deputy)) {
            $this->deputies->add($deputy);
            $deputy->setCourtOrder($this);
        }

        return $this;
    }
}
