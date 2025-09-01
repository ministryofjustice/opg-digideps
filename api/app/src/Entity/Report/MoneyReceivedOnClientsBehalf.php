<?php

declare(strict_types=1);

namespace App\Entity\Report;

use App\Entity\MoneyReceivedOnClientsBehalfInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;


#[ORM\Table(name: 'income_received_on_clients_behalf')]
#[ORM\Entity]
class MoneyReceivedOnClientsBehalf implements MoneyReceivedOnClientsBehalfInterface
{
    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id ?? Uuid::uuid4();
        $this->created = new DateTime();
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

    /**
     *
     *
     * @JMS\Groups({"client-benefits-check"})
     *
     * @JMS\Type("App\Entity\Report\ClientBenefitsCheck")
     */
    #[JoinColumn(name: 'client_benefits_check_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: ClientBenefitsCheck::class, inversedBy: 'moneyReceivedOnClientsBehalf', cascade: ['persist'])]
    private ClientBenefitsCheck $clientBenefitsCheck;

    /**
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("string")
     */
    #[ORM\Column(name: 'money_type', type: 'string', nullable: false)]
    private string $moneyType;

    /**
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("float")
     */
    #[ORM\Column(name: 'amount', type: 'decimal', precision: 14, scale: 2, nullable: true)]
    private ?float $amount;

    /**
     *
     * @JMS\Groups({"client-benefits-check"})
     * @JMS\Type("string")
     */
    #[ORM\Column(name: 'who_received_money', type: 'string', nullable: true)]
    private ?string $whoReceivedMoney;

    public function getClientBenefitsCheck(): ClientBenefitsCheck
    {
        return $this->clientBenefitsCheck;
    }

    public function setClientBenefitsCheck(ClientBenefitsCheck $clientBenefitsCheck): MoneyReceivedOnClientsBehalf
    {
        $this->clientBenefitsCheck = $clientBenefitsCheck;

        return $this;
    }

    public function getMoneyType(): string
    {
        return $this->moneyType;
    }

    public function setMoneyType(string $moneyType): MoneyReceivedOnClientsBehalf
    {
        $this->moneyType = $moneyType;

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

    public function getWhoReceivedMoney(): ?string
    {
        return $this->whoReceivedMoney;
    }

    public function setWhoReceivedMoney(?string $whoReceivedMoney): MoneyReceivedOnClientsBehalf
    {
        $this->whoReceivedMoney = $whoReceivedMoney;

        return $this;
    }
}
