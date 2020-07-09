<?php declare(strict_types=1);

namespace AppBundle\Entity;


use DateTime;
use Doctrine\Common\Collections\Collection;

class CourtOrder
{
    /**
     * @JMS\Type("DateTime")
     * @JMS\Groups({"court-order"})
     *
     * @var DateTime
     */
    private $orderDate;

    /**
     * @JMS\Type("array<AppBundle\Entity\CourtOrderDeputy>")
     * @JMS\Groups({"court-order"})
     *
     * @var Collection<CourtOrderDeputy>
     */
    private $deputies;

    /**
     * @return DateTime
     */
    public function getOrderDate(): DateTime
    {
        return $this->orderDate;
    }

    /**
     * @param DateTime $orderDate
     * @return CourtOrder
     */
    public function setOrderDate(DateTime $orderDate): self
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getDeputies(): Collection
    {
        return $this->deputies;
    }

    /**
     * @param Collection $deputies
     * @return CourtOrder
     */
    public function setDeputies(Collection $deputies): self
    {
        $this->deputies = $deputies;

        return $this;
    }
}
