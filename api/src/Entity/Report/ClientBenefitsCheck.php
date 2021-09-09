<?php

 declare(strict_types=1);

namespace App\Entity\Report;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="client_benefits_check")
 * @ORM\Entity
 */
class ClientBenefitsCheck
{
    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id ?? Uuid::uuid4();
        $this->created = new DateTime();
    }

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="uuid")
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(name="created_at", type="datetime",nullable=true)
     * @Gedmo\Timestampable(on="create")
     */
    private DateTime $created;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\Report", inversedBy="clientBenefitsCheck")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private Report $report;

    /**
     * @var string one of either [date in format MM/YYYY, currentlyChecking, neverChecked]
     *
     * @ORM\Column(name="when_last_checked_entitlement", type="string", nullable=false)
     */
    private $whenLastCheckedEntitlement;

    /**
     * @var string one of either [yes, no, doNotKnow]
     *
     * @ORM\Column(name="do_others_receive_income_on_clients_behalf", type="string", nullable=false)
     */
    private $doOthersReceiveIncomeOnClientsBehalf;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Report\IncomeReceivedOnClientsBehalf", mappedBy="clientBenefitsCheck", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private ArrayCollection $typesOfIncomeReceivedOnClientsBehalf;

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): ClientBenefitsCheck
    {
        $this->id = $id;

        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function setReport(Report $report): ClientBenefitsCheck
    {
        $this->report = $report;

        return $this;
    }

    public function getWhenLastCheckedEntitlement(): string
    {
        return $this->whenLastCheckedEntitlement;
    }

    public function setWhenLastCheckedEntitlement(string $whenLastCheckedEntitlement): ClientBenefitsCheck
    {
        $this->whenLastCheckedEntitlement = $whenLastCheckedEntitlement;

        return $this;
    }

    public function getDoOthersReceiveIncomeOnClientsBehalf(): string
    {
        return $this->doOthersReceiveIncomeOnClientsBehalf;
    }

    public function setDoOthersReceiveIncomeOnClientsBehalf(string $doOthersReceiveIncomeOnClientsBehalf): ClientBenefitsCheck
    {
        $this->doOthersReceiveIncomeOnClientsBehalf = $doOthersReceiveIncomeOnClientsBehalf;

        return $this;
    }

    public function getTypesOfIncomeReceivedOnClientsBehalf(): ArrayCollection
    {
        return $this->typesOfIncomeReceivedOnClientsBehalf;
    }

    /**
     * @param ArrayCollection $typesOfIncomeReceivedOnClientsBehalf
     */
    public function addTypesOfIncomeReceivedOnClientsBehalf(IncomeReceivedOnClientsBehalf $incomeReceivedOnClientsBehalf): ClientBenefitsCheck
    {
        if (!$this->typesOfIncomeReceivedOnClientsBehalf->contains($incomeReceivedOnClientsBehalf)) {
            $this->typesOfIncomeReceivedOnClientsBehalf->add($incomeReceivedOnClientsBehalf);
        }

        return $this;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): ClientBenefitsCheck
    {
        $this->created = $created;

        return $this;
    }
}
