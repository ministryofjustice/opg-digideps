<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @ORM\Table(name="config")
 * @ORM\Entity
 */
class Config
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="config_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="cleanup", type="boolean", nullable=true)
     */
    private $cleanup;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cleanup
     *
     * @param boolean $cleanup
     * @return Config
     */
    public function setCleanup($cleanup)
    {
        $this->cleanup = $cleanup;

        return $this;
    }

    /**
     * Get cleanup
     *
     * @return boolean 
     */
    public function getCleanup()
    {
        return $this->cleanup;
    }
}
