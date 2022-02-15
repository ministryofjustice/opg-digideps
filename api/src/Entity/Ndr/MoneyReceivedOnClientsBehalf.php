<?php

declare(strict_types=1);

namespace App\Entity\Ndr;

use App\Entity\MoneyReceivedOnClientsBehalfInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="odr_income_received_on_clients_behalf")
 * @ORM\Entity
 */
class MoneyReceivedOnClientsBehalf implements MoneyReceivedOnClientsBehalfInterface
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
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("string")
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(name="created_at", type="datetime",nullable=true)
     * @Gedmo\Timestampable(on="create")
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("DateTime<'Y-m-d'>")
     */
    private DateTime $created;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Ndr\ClientBenefitsCheck", inversedBy="incomeReceivedOnClientsBehalf", cascade={"persist"})
     * @JoinColumn(name="client_benefits_check_id", referencedColumnName="id")
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("App\Entity\Ndr\ClientBenefitsCheck")
     */
    private ClientBenefitsCheck $clientBenefitsCheck;

    /**
     * @ORM\Column(name="income_type", type="string", nullable=false)
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("string")
     */
    private string $incomeType;

    /**
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("float")
     */
    private ?float $amount;

    public function getClientBenefitsCheck(): ClientBenefitsCheck
    {
        return $this->clientBenefitsCheck;
    }

    public function setClientBenefitsCheck(ClientBenefitsCheck $clientBenefitsCheck): MoneyReceivedOnClientsBehalf
    {
        $this->clientBenefitsCheck = $clientBenefitsCheck;

        return $this;
    }

    public function getIncomeType(): string
    {
        return $this->incomeType;
    }

    public function setIncomeType(string $incomeType): MoneyReceivedOnClientsBehalf
    {
        $this->incomeType = $incomeType;

        return $this;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(UuidInterface $id): MoneyReceivedOnClientsBehalf
    {
        $this->id = $id;

        return $this;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): MoneyReceivedOnClientsBehalf
    {
        $this->created = $created;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): MoneyReceivedOnClientsBehalf
    {
        $this->amount = $amount;

        return $this;
    }
}
