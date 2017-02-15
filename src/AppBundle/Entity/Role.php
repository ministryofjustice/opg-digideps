<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * Roles.
 */
class Role implements RoleInterface
{
    const ADMIN = 'ROLE_ADMIN';
    const LAY_DEPUTY = 'ROLE_LAY_DEPUTY';
    const AD = 'ROLE_AD';
    const SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * Added via digideps:fixtures command.
     *
     * @JMS\Exclude
     */
    public static $allowedRoles = [
        self::ADMIN => ['OPG Admin', 1],
        self::LAY_DEPUTY => ['Lay Deputy', 2],
        'ROLE_PROFESSIONAL_DEPUTY' => ['Professional Deputy', 3],
        'ROLE_LOCAL_AUTHORITY_DEPUTY' => ['Local Authority Deputy', 4],
        self::AD => ['Assisted Digital', 5],
        self::SUPER_ADMIN => ['Super Admin', 6],
    ];

    /**
     * @deprecated ID shouldn't be used anymore anywhere
     *
     * @param int $id
     *
     * @return string
     */
    public static function idToName($id)
    {
        foreach(self::$allowedRoles as $name => $row) {
            if ($row[1] == $id) {
                return $name;
            }
        }
    }

    /**
     * @var int
     *
     * @JMS\Groups({"role"})
     */
    private $name;

    /**
     * Role constructor.
     * @param int $id
     */
    public function __construct($name)
    {
        $this->name = $name;
    }


    /**
     * @deprecated
     *
     * @JMS\VirtualProperty
     * @JMS\Groups({"role", "audit_log"})
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string ROLE_*
     * @JMS\VirtualProperty
     *
     * @JMS\Groups({"role", "audit_log"})
     */
    public function getRole()
    {
        return $this->name;
    }
}
