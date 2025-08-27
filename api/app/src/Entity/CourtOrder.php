<?php

namespace App\Entity;

use App\Repository\CourtOrderRepository;
use DateTime;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Report;
use App\Entity\Traits\CreateUpdateTimestamps;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Court Orders for clients.
 */
#[ORM\Table(name: 'court_order')]
#[ORM\Entity(repositoryClass: CourtOrderRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CourtOrder
{
    use CreateUpdateTimestamps;


    #[JMS\Type('integer')]
    #[JMS\Groups(['court-order-basic', 'court-order-full'])]
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'court_order_id_seq', allocationSize: 1, initialValue: 1)]
    private int $id;

    #[JMS\Type('string')]
    #[JMS\Groups(['court-order-basic', 'court-order-full', 'deputy-court-order-basic'])]
    #[ORM\Column(name: 'court_order_uid', type: 'string', length: 36, nullable: false, unique: true)]
    private string $courtOrderUid;

    /**
     * e.g. "pfa" or "hw".
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['court-order-basic', 'court-order-full'])]
    #[ORM\Column(name: 'order_type', type: 'string', length: 10, nullable: false)]
    private string $orderType;

    #[JMS\Type('string')]
    #[JMS\Groups(['court-order-basic', 'court-order-full'])]
    #[ORM\Column(name: 'status', type: 'string', length: 10, nullable: false)]
    private string $status;

    #[JMS\Type('datetime')]
    #[JMS\Groups(['court-order-basic', 'court-order-full'])]
    #[ORM\Column(name: 'order_made_date', type: 'datetime', nullable: false)]
    private DateTime $orderMadeDate;


    #[JMS\Type(Client::class)]
    #[JMS\Groups(['court-order-full'])]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'courtOrders')]
    private Client $client;


    #[JMS\Type(Ndr::class)]
    #[JMS\Groups(['court-order-full'])]
    #[ORM\JoinColumn(name: 'ndr_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Ndr::class, cascade: ['persist'], fetch: 'EAGER')]
    private ?Ndr $ndr = null;

    /**
     *
     *
     * @var Collection<int, Report>
     */
    #[JMS\Type('ArrayCollection<App\Entity\Report\Report>')]
    #[JMS\Groups(['court-order-full'])]
    #[ORM\JoinTable(name: 'court_order_report')]
    #[ORM\JoinColumn(name: 'court_order_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: Report::class, inversedBy: 'courtOrders', fetch: 'EXTRA_LAZY', cascade: ['persist'])]
    private Collection $reports;

    #[JMS\Type('ArrayCollection<App\Entity\CourtOrderDeputy>')]
    #[ORM\OneToMany(targetEntity: CourtOrderDeputy::class, mappedBy: 'courtOrder', fetch: 'EXTRA_LAZY', cascade: ['persist'])]
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

    public function getOrderMadeDate(): DateTime
    {
        return $this->orderMadeDate;
    }

    public function setOrderMadeDate(DateTime $orderMadeDate): CourtOrder
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
}
