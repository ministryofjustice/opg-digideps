<?php

declare(strict_types=1);

namespace App\Entity\Report;

use App\Entity\MoneyReceivedOnClientsBehalfInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="income_received_on_clients_behalf")
 *
 * @ORM\Entity
 */
class MoneyReceivedOnClientsBehalf implements MoneyReceivedOnClientsBehalfInterface
{
    public function __construct(UuidInterface $id = null)
    {
        $this->id = $id ?? Uuid::uuid4();
        $this->created = new \DateTime();
    }

    /**
     * @ORM\Id
     *
     * @ORM\Column(name="id", type="uuid")
     *
     * @ORM\GeneratedValue(strategy="NONE")
     *
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     *
     *
     */
    #[JMS\Groups(['client-benefits-check'])]
    #[JMS\Type('string')]
    private UuidInterface $id;

    /**
     * @ORM\Column(name="created_at", type="datetime",nullable=true)
     *
     * @Gedmo\Timestampable(on="create")
     *
     *
     */
    #[JMS\Groups(['client-benefits-check'])]
    #[JMS\Type("DateTime<'Y-m-d'>")]
    private \DateTime $created;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\ClientBenefitsCheck", inversedBy="moneyReceivedOnClientsBehalf", cascade={"persist"})
     *
     * @JoinColumn(name="client_benefits_check_id", referencedColumnName="id")
     *
     *
     */
    #[JMS\Groups(['client-benefits-check'])]
    #[JMS\Type('App\Entity\Report\ClientBenefitsCheck')]
    private ClientBenefitsCheck $clientBenefitsCheck;

    /**
     * @ORM\Column(name="money_type", type="string", nullable=false)
     *
     *
     */
    #[JMS\Groups(['client-benefits-check'])]
    #[JMS\Type('string')]
    private string $moneyType;

    /**
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     *
     *
     */
    #[JMS\Groups(['client-benefits-check'])]
    #[JMS\Type('float')]
    private ?float $amount;

    /**
     * @ORM\Column(name="who_received_money", type="string", nullable=true)
     *
     *
     */
    #[JMS\Groups(['client-benefits-check'])]
    #[JMS\Type('string')]
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

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function setCreated(\DateTime $created): MoneyReceivedOnClientsBehalf
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
