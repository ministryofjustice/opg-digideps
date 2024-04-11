<?php

declare(strict_types=1);

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
     * @ORM\Column(name="court_order_uid", type="integer", length=20, nullable=false, unique=true)
     */
    private $courtOrderUid;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(type="string", name="comments", length=10, nullable=true)
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

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    public function getCourtOrderUid(): int
    {
        return $this->courtOrderUid;
    }

    /**
     * @return $this
     */
    public function setCourtOrderUid(int $courtOrderUid)
    {
        $this->courtOrderUid = $courtOrderUid;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return $this
     */
    public function setActive(bool $active)
    {
        $this->active = $active;

        return $this;
    }
}
