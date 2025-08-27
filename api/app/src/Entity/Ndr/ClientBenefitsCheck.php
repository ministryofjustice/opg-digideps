<?php

declare(strict_types=1);

namespace App\Entity\Ndr;

use App\Entity\ClientBenefitsCheckInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;


#[ORM\Table(name: 'odr_client_benefits_check')]
#[ORM\Entity]
class ClientBenefitsCheck implements ClientBenefitsCheckInterface
{
    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id ?? Uuid::uuid4();
        $this->created = new DateTime();
        $this->typesOfMoneyReceivedOnClientsBehalf = new ArrayCollection();
    }

    /**
     *
     *
     *
     *
     * @JMS\Groups({"client-benefits-check"})
     *
     * @JMS\Type("string")
     */
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    /**
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private DateTime $created;


    #[ORM\JoinColumn(name: 'ndr_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    #[ORM\OneToOne(targetEntity: Ndr::class, inversedBy: 'clientBenefitsCheck')]
    private ?Ndr $report;

    /**
     * @var string one of either [haveChecked, currentlyChecking, neverChecked]
     *
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("string")
     */
    #[ORM\Column(name: 'when_last_checked_entitlement', type: 'string', nullable: false)]
    private $whenLastCheckedEntitlement;

    /**
     * @var DateTime
     *
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    #[ORM\Column(name: 'date_last_checked_entitlement', type: 'datetime', nullable: true)]
    private $dateLastCheckedEntitlement;

    /**
     * @var string
     *
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("string")
     */
    #[ORM\Column(name: 'never_checked_explanation', type: 'text', nullable: true)]
    private $neverCheckedExplanation;

    /**
     * @var string one of either [yes, no, doNotKnow]
     *
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("string")
     */
    #[ORM\Column(name: 'do_others_receive_money_on_clients_behalf', type: 'string', nullable: true)]
    private $doOthersReceiveMoneyOnClientsBehalf;

    /**
     * @var string|null
     *
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("string")
     */
    #[ORM\Column(name: 'dont_know_money_explanation', type: 'text', nullable: true)]
    private $dontKnowMoneyExplanation;

    /**
     *
     * @JMS\Groups({"client-benefits-check"})
     *
     * @JMS\Type("ArrayCollection<App\Entity\Ndr\MoneyReceivedOnClientsBehalf>")
     */
    #[ORM\OneToMany(targetEntity: MoneyReceivedOnClientsBehalf::class, mappedBy: 'clientBenefitsCheck', cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    #[OrderBy(['created' => 'ASC'])]
    private $typesOfMoneyReceivedOnClientsBehalf;

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): ClientBenefitsCheck
    {
        $this->id = $id;

        return $this;
    }

    public function getWhenLastCheckedEntitlement(): ?string
    {
        return $this->whenLastCheckedEntitlement;
    }

    public function setWhenLastCheckedEntitlement(?string $whenLastCheckedEntitlement): ClientBenefitsCheck
    {
        $this->whenLastCheckedEntitlement = $whenLastCheckedEntitlement;

        if (self::WHEN_CHECKED_IVE_NEVER_CHECKED !== $whenLastCheckedEntitlement) {
            $this->setNeverCheckedExplanation(null);
        }

        if (self::WHEN_CHECKED_I_HAVE_CHECKED !== $whenLastCheckedEntitlement) {
            $this->setDateLastCheckedEntitlement(null);
        }

        return $this;
    }

    public function getDoOthersReceiveMoneyOnClientsBehalf(): ?string
    {
        return $this->doOthersReceiveMoneyOnClientsBehalf;
    }

    public function setDoOthersReceiveMoneyOnClientsBehalf(?string $doOthersReceiveMoneyOnClientsBehalf): ClientBenefitsCheck
    {
        $this->doOthersReceiveMoneyOnClientsBehalf = $doOthersReceiveMoneyOnClientsBehalf;

        if (self::OTHER_MONEY_DONT_KNOW !== $doOthersReceiveMoneyOnClientsBehalf) {
            $this->setDontKnowMoneyExplanation(null);
        }

        return $this;
    }

    public function getTypesOfMoneyReceivedOnClientsBehalf(): Collection
    {
        return $this->typesOfMoneyReceivedOnClientsBehalf;
    }

    public function addTypeOfMoneyReceivedOnClientsBehalf(?MoneyReceivedOnClientsBehalf $moneyReceivedOnClientsBehalf): ClientBenefitsCheck
    {
        if (!$this->typesOfMoneyReceivedOnClientsBehalf->contains($moneyReceivedOnClientsBehalf)) {
            $this->typesOfMoneyReceivedOnClientsBehalf->add($moneyReceivedOnClientsBehalf);
            $moneyReceivedOnClientsBehalf->setClientBenefitsCheck($this);
        }

        return $this;
    }

    public function emptyTypeOfMoneyReceivedOnClientsBehalf(): ClientBenefitsCheck
    {
        $this->typesOfMoneyReceivedOnClientsBehalf = new ArrayCollection();

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

    public function getDateLastCheckedEntitlement(): ?DateTime
    {
        return $this->dateLastCheckedEntitlement;
    }

    public function setDateLastCheckedEntitlement(?DateTime $dateLastCheckedEntitlement): ClientBenefitsCheck
    {
        $this->dateLastCheckedEntitlement = $dateLastCheckedEntitlement;

        return $this;
    }

    public function getNeverCheckedExplanation(): ?string
    {
        return $this->neverCheckedExplanation;
    }

    public function setNeverCheckedExplanation(?string $neverCheckedExplanation): ClientBenefitsCheck
    {
        $this->neverCheckedExplanation = $neverCheckedExplanation;

        return $this;
    }

    public function getDontKnowMoneyExplanation(): ?string
    {
        return $this->dontKnowMoneyExplanation;
    }

    public function setDontKnowMoneyExplanation(?string $dontKnowMoneyExplanation): ClientBenefitsCheck
    {
        $this->dontKnowMoneyExplanation = $dontKnowMoneyExplanation;

        return $this;
    }

    public function getReport(): ?Ndr
    {
        return $this->report;
    }

    public function setReport(?Ndr $report): ClientBenefitsCheck
    {
        $this->report = $report;

        return $this;
    }
}
