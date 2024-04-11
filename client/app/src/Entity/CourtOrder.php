<?php

namespace App\Entity;

use JMS\Serializer\Annotation as JMS;

/**
 * Court Orders for clients.
 */
class CourtOrder
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $courtOrderUid;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $type;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
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
