<?php
namespace AppBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * @codeCoverageIgnore
 * @JMS\XmlRoot("user")
 * @JMS\AccessType("public_method")
 * @JMS\ExclusionPolicy("none")
 */
class Role
{ 
    /**
     * @JMS\Type("integer")
     * @var integer
     */
    private $id;
    
    /**
     * @JMS\Type("string")
     */
    private $name;
    
    /**
     * @JMS\Type("string")
     */
    private $role;


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
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    
    /**
     * Set name
     *
     * @param string $name
     * @return Role
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
    
    public function getRole()
    {
        return $this->role;
    }
 
    /**
     * Set role
     *
     * @param string $role
     * @return Role
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

}
