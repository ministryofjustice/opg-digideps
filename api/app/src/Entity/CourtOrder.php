<?php

namespace App\Entity;

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
     * @JMS\Type("integer")
     *
     * @JMS\Groups({"court-order-basic"})
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="court_order_id_seq", allocationSize=1, initialValue=1)
     */
    private int $id;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"court-order-basic", "court-order-full"})
     *
     * @ORM\Column(name="court_order_uid", type="string", length=36, nullable=false, unique=true)
     */
    private string $courtOrderUid;

    /**
     * @JMS\Type("string")
     *
     * @JMS\Groups({"court-order-basic", "court-order-full"})
     *
     * @ORM\Column(name="type", type="string", length=10, nullable=false)
     */
    private string $type;

    /**
     * @JMS\Type("boolean")
     *
     * @JMS\Groups({"court-order-basic", "court-order-full"})
     *
     * @ORM\Column(name="active", type="boolean", options = { "default": true })
     */
    private bool $active;

    /**
     * @JMS\Type("ArrayCollection<App\Entity\Report\Report>")
     *
     * @JMS\Groups({"court-order-full"})
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Report\Report", inversedBy="courtOrders", fetch="EXTRA_LAZY", cascade={"persist"})
     *
     * @ORM\JoinTable(name="court_order_report",
     *         joinColumns={@ORM\JoinColumn(name="court_order_id", referencedColumnName="id", onDelete="CASCADE")},
     *         inverseJoinColumns={@ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")}
     *     )
     *
     * @var Collection<int, Report>
     */
    private Collection $reports;

    /**
     * @JMS\Type("App\Entity\Client")
     *
     * @JMS\Groups({"court-order-full"})
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="courtOrders")
     *
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private Client $client;

    /**
     * @JMS\Type("ArrayCollection<App\Entity\CourtOrderDeputy>")
     *
     * @ORM\OneToMany(targetEntity="App\Entity\CourtOrderDeputy", mappedBy="courtOrder", cascade={"persist"})
     */
    private Collection $courtOrderDeputyRelationships;

    public function __construct()
    {
        $this->courtOrderDeputyRelationships = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    /**
     * active means "not discharged".
     *
     * @JMS\VirtualProperty
     *
     * @JMS\Groups({"court-order-full"})
     *
     * @return Deputy[]
     */
    public function getActiveDeputies(): array
    {
        $activeDeputies = [];

        /** @var CourtOrderDeputy $rel */
        foreach ($this->courtOrderDeputyRelationships as $rel) {
            if (!$rel->isDischarged()) {
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

    public function getCourtOrderUid(): int
    {
        return $this->courtOrderUid;
    }

    public function setCourtOrderUid(int $courtOrderUid): CourtOrder
    {
        $this->courtOrderUid = $courtOrderUid;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): CourtOrder
    {
        $this->type = $type;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): CourtOrder
    {
        $this->active = $active;

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

    public function addReport(Report $report): CourtOrder
    {
        $this->reports->add($report);

        return $this;
    }
}
