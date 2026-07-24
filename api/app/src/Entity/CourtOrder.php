<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Common\CourtOrder\CourtOrderKind;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Common\Deputy\DeputyType;
use OPG\Digideps\Common\Report\ReportType;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Traits\CreateUpdateTimestamps;
use OPG\Digideps\Backend\Repository\CourtOrderRepository;

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
    private ?int $id = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['court-order-basic', 'court-order-full', 'deputy-court-order-basic'])]
    #[ORM\Column(name: 'court_order_uid', type: 'string', length: 36, unique: true, nullable: false)]
    private string $courtOrderUid;

    /**
     * @see CourtOrderType
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['court-order-basic', 'court-order-full'])]
    #[ORM\Column(name: 'order_type', type: 'string', length: 3, nullable: false)]
    private string $orderType;

    /**
     * @see CourtOrderReportType
     */
    #[ORM\Column(name: 'order_report_type', type: 'string', length: 6, nullable: false)]
    private string $orderReportType;

    #[JMS\Type('string')]
    #[JMS\Groups(['court-order-basic', 'court-order-full'])]
    #[ORM\Column(name: 'status', type: 'string', length: 10, nullable: false)]
    private string $status;

    #[JMS\Type('datetime')]
    #[JMS\Groups(['court-order-basic', 'court-order-full'])]
    #[ORM\Column(name: 'order_made_date', type: 'datetime', nullable: false)]
    private \DateTime $orderMadeDate;

    #[JMS\Type('OPG\Digideps\Backend\Entity\Client')]
    #[JMS\Groups(['court-order-full'])]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'courtOrders')]
    private Client $client;

    #[ORM\JoinColumn(name: 'sibling_id', referencedColumnName: 'id')]
    #[ORM\OneToOne(targetEntity: CourtOrder::class)]
    private ?CourtOrder $sibling = null;

    /**
     * @see CourtOrderKind
     */
    #[ORM\Column(name: 'order_kind', type: 'string', length: 6, nullable: false)]
    private string $orderKind;

    /**
     * @var Collection<int, Report> $reports
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\Report\Report>')]
    #[JMS\Groups(['court-order-full'])]
    #[ORM\JoinTable(name: 'court_order_report')]
    #[ORM\JoinColumn(name: 'court_order_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: Report::class, inversedBy: 'courtOrders', cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private Collection $reports;

    /**
     * @var Collection<int, CourtOrderDeputy>
     */
    #[JMS\Type('ArrayCollection<OPG\Digideps\Backend\Entity\CourtOrderDeputy>')]
    #[ORM\OneToMany(mappedBy: 'courtOrder', targetEntity: CourtOrderDeputy::class, cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    private Collection $courtOrderDeputyRelationships;

    private ?ReportType $desiredReportType = null;

    public function __construct(
        string $courtOrderUid,
        CourtOrderType $orderType,
        CourtOrderReportType $orderReportType,
        CourtOrderKind $orderKind,
        \DateTime $orderMadeDate,
        Client $client,
        string $status = 'ACTIVE'
    ) {
        $this->courtOrderDeputyRelationships = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->courtOrderUid = $courtOrderUid;
        $this->orderType = $orderType->value;
        $this->orderReportType = $orderReportType->value;
        $this->orderMadeDate = $orderMadeDate;
        $this->client = $client;
        $this->orderKind = $orderKind->value;
        $this->status = $status;
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
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

        return $this;
    }

    public function getCourtOrderUid(): string
    {
        return $this->courtOrderUid;
    }

    public function setCourtOrderUid(string $courtOrderUid): static
    {
        $this->courtOrderUid = $courtOrderUid;

        return $this;
    }

    public function getOrderType(): CourtOrderType
    {
        return CourtOrderType::from($this->orderType);
    }

    public function setOrderType(CourtOrderType $orderType): static
    {
        $this->orderType = $orderType->value;

        return $this;
    }

    public function getOrderReportType(): CourtOrderReportType
    {
        $fallBack = $this->getOrderKind() === CourtOrderKind::Hybrid || $this->getOrderType() === CourtOrderType::PFA ?
            CourtOrderReportType::OPG102 :
            CourtOrderReportType::OPG104;

        return CourtOrderReportType::tryFrom(strtoupper($this->orderReportType)) ?? $fallBack;
    }

    public function setOrderReportType(CourtOrderReportType $orderReportType): static
    {
        $this->orderReportType = $orderReportType->value;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getOrderMadeDate(): \DateTime
    {
        return $this->orderMadeDate;
    }

    public function setOrderMadeDate(\DateTime $orderMadeDate): static
    {
        $this->orderMadeDate = $orderMadeDate;

        return $this;
    }

    public function getSibling(): ?CourtOrder
    {
        return $this->sibling;
    }

    public function setSibling(?CourtOrder $sibling): static
    {
        $this->sibling = $sibling;

        return $this;
    }

    public function getOrderKind(): CourtOrderKind
    {
        return CourtOrderKind::from($this->orderKind);
    }

    public function setOrderKind(CourtOrderKind $kind): static
    {
        $this->orderKind = $kind->value;

        return $this;
    }

    public function addReport(Report $report): static
    {
        $this->reports->add($report);

        return $this;
    }

    public function removeReport(Report $report): static
    {
        $this->reports->removeElement($report);

        return $this;
    }

    /**
     * @return Collection<int, CourtOrderDeputy>
     */
    public function getDeputyRelationships(): Collection
    {
        return $this->courtOrderDeputyRelationships;
    }

    /**
     * @return Collection<int, Report>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    /**
     * Get the most recent report for this CourtOrder
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

    public function isSingle(): bool
    {
        return $this->getOrderKind() === CourtOrderKind::Single;
    }

    public function isDual(): bool
    {
        return $this->getOrderKind() === CourtOrderKind::Dual;
    }

    public function isHybrid(): bool
    {
        return $this->getOrderKind() === CourtOrderKind::Hybrid;
    }

    public function getDesiredReportType(): ReportType
    {
        if ($this->desiredReportType === null) {
            $deputyType = DeputyType::LAY;
            foreach ($this->getActiveDeputies() as $deputy) {
                if ($deputy->getDeputyType() !== DeputyType::LAY) {
                    $deputyType = $deputy->getDeputyType();
                    //PA and PROF are mutually exclusive in valid data and have higher priority than LAY.
                    break;
                }
            }

            $courtOrderReportType = $this->getOrderReportType();
            if ($this->getOrderKind() === CourtOrderKind::Hybrid && $courtOrderReportType === CourtOrderReportType::OPG104) {
                $courtOrderReportType = $this->getSibling()?->getOrderReportType() ?? CourtOrderReportType::OPG103;
            }

            $this->desiredReportType = new ReportType(
                $courtOrderReportType,
                $this->getOrderType(),
                $this->getOrderKind(),
                $deputyType
            );
        }

        return $this->desiredReportType;
    }
}
