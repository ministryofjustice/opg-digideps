<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * CourtOrderType.
 *
 * @JMS\XmlRoot("court_order_type")
 * @ORM\Table(name="court_order_type")
 * @ORM\Entity
 */
class CourtOrderType
{
    /**
     * @JMS\Exclude
     */
    public static $fixtures = [
        1 => 'Personal Welfare',
        2 => 'Property and Affairs'
    ];

    /**
     * @var int
     * @JMS\Type("integer")
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="court_order_type_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     * @JMS\Type("string")
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return CourtOrderType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}
