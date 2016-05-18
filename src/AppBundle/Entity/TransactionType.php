<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="transaction_type")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"in" = "TransactionTypeIn", "out" = "TransactionTypeOut"})
 */
abstract class TransactionType
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="string", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * Discriminator (in/out).
     *
     * @var string
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="has_more_details", type="boolean", nullable=false)
     */
    private $hasMoreDetails;

    /**
     * @var int
     *
     * @ORM\Column(name="display_order", type="integer", nullable=true)
     */
    private $displayOrder;

    /**
     * @var TransactionTypeCategory
     *
     * @ORM\Column(name="category", type="string", nullable=false)
     *
     * @JMS\Type("string")
     */
    private $category;

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getHasMoreDetails()
    {
        return $this->hasMoreDetails;
    }

    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function setHasMoreDetails($hasMoreDetails)
    {
        $this->hasMoreDetails = $hasMoreDetails;

        return $this;
    }

    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;

        return $this;
    }

    /**
     * @return TransactionTypeCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param TransactionTypeCategory $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }
}
