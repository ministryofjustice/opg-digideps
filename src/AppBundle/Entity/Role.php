<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * Roles.
 *
 * @ORM\Table(name="role")
 * @ORM\Entity
 */
class Role implements RoleInterface
{
    const ROLE_ADMIN = 1;
    const ROLE_LAY_DEPUTY = 2;

    const ADMIN = 'ROLE_ADMIN';
    const LAY_DEPUTY = 'ROLE_LAY_DEPUTY';

    /**
     * @var int
     *
     * @JMS\Groups({"basic"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="role_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @JMS\Groups({"basic", "audit_log"})
     * @ORM\Column(name="name", type="string", length=60 )
     */
    private $name;

    /**
     * @JMS\Exclude
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\User", mappedBy="role" )
     */
    private $user;

    /**
     * @JMS\Groups({"basic"})
     * @ORM\Column( name="role", type="string", length=50, nullable=true)
     */
    private $role;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->user = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Role
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

    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set role.
     *
     * @param string $role
     *
     * @return Role
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Add user.
     *
     * @param User $user
     *
     * @return Role
     */
    public function addUser(User $user)
    {
        $this->user[] = $user;

        return $this;
    }

    /**
     * Remove user.
     *
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->user->removeElement($user);
    }

    /**
     * Get user.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUser()
    {
        return $this->user;
    }
}
