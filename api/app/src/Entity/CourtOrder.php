<?php

namespace App\Entity;

use App\Entity\Traits\CreateUpdateTimestamps;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Court Orders for clients.
 *
 * @ORM\Table(name="court_order")
 *
 * @ORM\Entity()
 *
 * @ORM\HasLifecycleCallbacks()
 */
class CourtOrder
{
    use CreateUpdateTimestamps;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="court_order_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     *
     * @ORM\Column(name="court_order_uid", type="bigint", nullable=false, unique=true)
     */
    private $courtOrderUid;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(name="type", type="string", length=10, nullable=false)
     */
    private $type;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     *
     * @ORM\Column(name="active", type="boolean", options = { "default": true })
     */
    private $active;

    /**
     * @var Client
     *
     * @JMS\Type("App\Entity\Client")
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Client", inversedBy="courtOrders", fetch="EAGER")
     *
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    private $client;

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

    /**
     * @return CourtOrder
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }
}
