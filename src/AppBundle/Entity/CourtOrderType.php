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
    // Don't change the numbers, or behat tests will fail
    
    const PERSONAL_WELFARE = 1;//Personal Welfare
    const PROPERTY_AND_AFFAIRS = 2; //Property and Affairs
    const PROPERTY_AND_AFFAIRS_AND_PERSONAL_WELFARE = 3;
    
   /**
    * @return array
    */
   public static function getCourtOrderTypesArray()
    {
        return array(
            self::PROPERTY_AND_AFFAIRS => 'Property and Affairs',
            self::PERSONAL_WELFARE => 'Personal Welfare',
            self::PROPERTY_AND_AFFAIRS_AND_PERSONAL_WELFARE => 'Property and Affairs & Personal Welfare'
        );
    }
    
    
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Client", mappedBy="courtOrderType")
     */
    private $clients;
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->clients = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
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
     * Add cases
     *
     * @param \AppBundle\Entity\Client $client
     * @return CourtOrderType
     */
    public function addClient(\AppBundle\Entity\Client $client)
    {
        $this->clients[] = $client;

        return $this;
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
     * Get clients
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getClients()
    {
        return $this->clients;
    }
}
