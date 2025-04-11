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
    private $orderType;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $status;

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

    public function getOrderType(): string
    {
        return $this->orderType;
    }

    public function setOrderType(string $orderType): CourtOrder
    {
        $this->orderType = $orderType;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): CourtOrder
    {
        $this->status = $status;

        return $this;
    }
}
