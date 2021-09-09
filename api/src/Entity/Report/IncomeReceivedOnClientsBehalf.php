<?php

declare(strict_types=1);

namespace App\Entity\Report;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="income_received_on_clients_behalf")
 * @ORM\Entity
 */
class IncomeReceivedOnClientsBehalf
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
     * @ORM\OneToOne(targetEntity="App\Entity\Report\ClientBenefitsCheck", inversedBy="incomeReceivedOnClientsBehalf", cascade={"persist", "remove"})
     */
    private ClientBenefitsCheck $clientBenefitsCheck;

    /**
     * @ORM\Column(name="income_type", type="string", nullable=false)
     */
    private string $incomeType;

    /**
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    private float $amount;

    public function getClientBenefitsCheck(): ClientBenefitsCheck
    {
        return $this->clientBenefitsCheck;
    }

    public function setClientBenefitsCheck(ClientBenefitsCheck $clientBenefitsCheck): IncomeReceivedOnClientsBehalf
    {
        $this->clientBenefitsCheck = $clientBenefitsCheck;

        return $this;
    }

    public function getIncomeType(): string
    {
        return $this->incomeType;
    }

    public function setIncomeType(string $incomeType): IncomeReceivedOnClientsBehalf
    {
        $this->incomeType = $incomeType;

        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): IncomeReceivedOnClientsBehalf
    {
        $this->amount = $amount;

        return $this;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): IncomeReceivedOnClientsBehalf
    {
        $this->id = $id;

        return $this;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): IncomeReceivedOnClientsBehalf
    {
        $this->created = $created;

        return $this;
    }
}
