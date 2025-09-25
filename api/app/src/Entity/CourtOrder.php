<?php

namespace App\Entity;

use App\Entity\Ndr\Ndr;
use App\Entity\Report\Report;
use App\Entity\Traits\CreateUpdateTimestamps;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Court Orders for clients.
 *
 * @ORM\Table(name="court_order")
 *
 * @ORM\Entity(repositoryClass="App\Repository\CourtOrderRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class CourtOrder
{
    use CreateUpdateTimestamps;

    /**
     * @ORM\Id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="court_order_id_seq", allocationSize=1, initialValue=1)
     */
    #[JMS\Type('integer')]
    #[JMS\Groups(['court-order-basic', 'court-order-full'])]
    private int $id;

    /**
     * @ORM\Column(name="court_order_uid", type="string", length=36, nullable=false, unique=true)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['court-order-basic', 'court-order-full', 'deputy-court-order-basic'])]
    private string $courtOrderUid;

    /**
     * e.g. "pfa" or "hw".
     *
     * @ORM\Column(name="order_type", type="string", length=10, nullable=false)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['court-order-basic', 'court-order-full'])]
    private string $orderType;

    /**
     * @ORM\Column(name="status", type="string", length=10, nullable=false)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['court-order-basic', 'court-order-full'])]
    private string $status;

    /**
     * @ORM\Column(name="order_made_date", type="datetime", nullable=false)
     */
    #[JMS\Type('datetime')]
    #[JMS\Groups(['court-order-basic', 'court-order-full'])]
    private \DateTime $orderMadeDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="courtOrders")
     *
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    #[JMS\Type(Client::class)]
    #[JMS\Groups(['court-order-full'])]
    private Client $client;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ndr\Ndr", cascade={"persist"}, fetch="EAGER")
     *
     * @ORM\JoinColumn(name="ndr_id", referencedColumnName="id", onDelete="SET NULL")
     */
    #[JMS\Type(Ndr::class)]
    #[JMS\Groups(['court-order-full'])]
    private ?Ndr $ndr = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Report\Report", inversedBy="courtOrders", fetch="EXTRA_LAZY", cascade={"persist"})
     *
     * @ORM\JoinTable(name="court_order_report",
     *         joinColumns={@ORM\JoinColumn(name="court_order_id", referencedColumnName="id", onDelete="CASCADE")},
     *         inverseJoinColumns={@ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")}
     *     )
     *
     * @var Collection<int, Report>
     */
    #[JMS\Type('ArrayCollection<App\Entity\Report\Report>')]
    #[JMS\Groups(['court-order-full'])]
    private Collection $reports;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CourtOrderDeputy", mappedBy="courtOrder", fetch="EXTRA_LAZY", cascade={"persist"})
     */
    #[JMS\Type('ArrayCollection<App\Entity\CourtOrderDeputy>')]
    private Collection $courtOrderDeputyRelationships;

    public function __construct()
    {
        $this->courtOrderDeputyRelationships = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    /**
     * active means "not discharged".
     *
     * @return Deputy[]
     */
    #[JMS\VirtualProperty]
    #[JMS\Groups(['court-order-full'])]
    public function getActiveDeputies(): array
    {
        $activeDeputies = [];

        /** @var CourtOrderDeputy $rel */
        foreach ($this->courtOrderDeputyRelationships as $rel) {
            if ($rel->isActive()) {
                $activeDeputies[] = $rel->getDeputy();
            }
        }

        return $activeDeputies;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): CourtOrder
    {
        $this->id = $id;

        return $this;
    }

    public function getCourtOrderUid(): string
    {
        return $this->courtOrderUid;
    }

    public function setCourtOrderUid(string $courtOrderUid): CourtOrder
    {
        $this->courtOrderUid = $courtOrderUid;

        return $this;
    }

    public function getOrderType(): string
    {
        return $this->orderType;
    }

    public function setOrderType(string $orderType): CourtOrder
    {
        $this->orderType = $orderType;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): CourtOrder
    {
        $this->status = $status;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): CourtOrder
    {
        $this->client = $client;

        return $this;
    }

    public function getOrderMadeDate(): \DateTime
    {
        return $this->orderMadeDate;
    }

    public function setOrderMadeDate(\DateTime $orderMadeDate): CourtOrder
    {
        $this->orderMadeDate = $orderMadeDate;

        return $this;
    }

    public function addReport(Report $report): CourtOrder
    {
        $this->reports->add($report);

        return $this;
    }

    /**
     * @return Collection<int, CourtOrderDeputy>
     */
    public function getDeputyRelationships(): Collection
    {
        return $this->courtOrderDeputyRelationships;
    }

    public function setNdr(Ndr $ndr): CourtOrder
    {
        $this->ndr = $ndr;

        return $this;
    }

    public function getNdr(): ?Ndr
    {
        return $this->ndr;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    /**
     * Get the report with the latest due date for this CourtOrder
     */
    public function getLatestReport(): ?Report
    {
        /** @var ?Report $latest */
        $latest = null;

        foreach ($this->reports as $report) {
            if (is_null($latest) || $report->getStartDate() > $latest->getStartDate()) {
                $latest = $report;
            }
        }

        return $latest;
    }
}
