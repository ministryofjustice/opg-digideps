<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Roles
 *
 * @ORM\Table(name="role")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RoleRepository")
 */
class Role
{
    const OPG_ADMINISTRATOR = 1; // OPG Administrator
    const LAY_DEPUTY = 2; // Lay Deputy
    const PROFESSIONAL_DEPUTY = 3; //Professional Deputy
    const LOCAL_AUTHORITY_DEPUTY = 4; //Local Authority Deputy
    const VISITOR = 5; //Visitor
    const GUEST = 6; // (doesn't need a db record, only used in ACL)
    
    public static $roles = array(
        self::OPG_ADMINISTRATOR => 'OPG Administrator',
        self::LAY_DEPUTY => 'Lay Deputy',
        self::PROFESSIONAL_DEPUTY => 'Professional Deputy',
        self::LOCAL_AUTHORITY_DEPUTY => 'Local Authority Deputy',
        self::VISITOR => 'Visitor'
    );
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="role_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=60, nullable=true)
     */
    private $name;
    
    /**
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\User", mappedBy="roles", cascade={"persist"})
     */
    private $users;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Add users
     *
     * @param \AppBundle\Entity\User $users
     * @return Role
     */
    public function addUser(\AppBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \AppBundle\Entity\User $users
     */
    public function removeUser(\AppBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }
}
