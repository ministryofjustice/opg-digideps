<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CourtOrderType
 *
 * @ORM\Table(name="court_order_type")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CourtOrderTypeRepository")
 */
class CourtOrderType
{ 
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="court_order_type_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Report", mappedBy="courtOrderType")
     */
    private $reports;
    
   
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reports = new \Doctrine\Common\Collections\ArrayCollection();
    }
        
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
     * Set name
     *
     * @param string $name
     * @return CourtOrderType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Remove cases
     *
     * @param \AppBundle\Entity\Client $client
     */
    public function removeCase(\AppBundle\Entity\Client $client)
    {
        $this->clients->removeElement($client);
    }
    
    /**
     * Add reports
     *
     * @param \AppBundle\Entity\Report $reports
     * @return CourtOrderType
     */
    public function addReport(\AppBundle\Entity\Report $reports)
    {
        $this->reports[] = $reports;

        return $this;
    }

    /**
     * Remove reports
     *
     * @param \AppBundle\Entity\Report $reports
     */
    public function removeReport(\AppBundle\Entity\Report $reports)
    {
        $this->reports->removeElement($reports);
    }

    /**
     * Get reports
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getReports()
    {
        return $this->reports;
    }
}
