<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Roles.
 *
 * @ORM\Table(name="role")
 * @ORM\Entity
 */
class Role implements RoleInterface
{
    const ADMIN = 'ROLE_ADMIN';
    const LAY_DEPUTY = 'ROLE_LAY_DEPUTY';
    const AD = 'ROLE_AD';
    const SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * Added via digideps:fixtures command.
     * //TODO remove this table, role name should be used inside user table with no joins
     *
     * @JMS\Exclude
     */
    public static $fixtures = [
        1 => ['OPG Admin', self::ADMIN],
        2 => ['Lay Deputy', self::LAY_DEPUTY],
        3 => ['Professional Deputy', 'ROLE_PROFESSIONAL_DEPUTY'],
        4 => ['Local Authority Deputy', 'ROLE_LOCAL_AUTHORITY_DEPUTY'],
        5 => ['Assisted Digital', self::AD],
        6 => ['Super Admin', self::SUPER_ADMIN],
    ];

    /**
     * @var int
     *
     * @JMS\Groups({"role"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="role_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @JMS\Groups({"role"})
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
     * Get name.
     *
     * @JMS\VirtualProperty
     * @JMS\Groups({"role", "audit_log"})
     *
     * @return string
     */
    public function getName()
    {
        return self::$fixtures[$this->getId()][0];
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
