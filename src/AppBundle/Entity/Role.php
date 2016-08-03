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
     * Added via digideps:fixtures command.
     *
     * @JMS\Exclude
     */
    public static $fixtures = [
        self::ROLE_ADMIN => ['OPG Administrator', self::ADMIN],
        self::ROLE_LAY_DEPUTY => ['Lay Deputy', self::LAY_DEPUTY],
        3 => ['Professional Deputy', 'ROLE_PROFESSIONAL_DEPUTY'],
        4 => ['Local Authority Deputy', 'ROLE_LOCAL_AUTHORITY_DEPUTY'],
        5 => ['Assisted Digital Support', 'ROLE_AD'],
    ];

    /**
     * @var int
     *
     * @JMS\Groups({"basic", "role"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="role_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @JMS\Groups({"basic", "audit_log", "role"})
     * @ORM\Column(name="name", type="string", length=60 )
     */
    private $name;

    /**
     * @JMS\Groups({"basic", "role"})
     * @ORM\Column( name="role", type="string", length=50, nullable=true)
     */
    private $role;

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
}
