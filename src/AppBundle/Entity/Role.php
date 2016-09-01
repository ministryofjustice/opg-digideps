<?php

namespace AppBundle\Entity;

use JMS\Serializer\Annotation as JMS;

/**
 * @codeCoverageIgnore
 * @JMS\XmlRoot("user")
 * @JMS\AccessType("public_method")
 * @JMS\ExclusionPolicy("none")
 */
class Role
{
    public static $availableRoles = [
        1 => 'OPG Administrator',
        2 => 'Lay Deputy',
//        3 => "Professional Deputy",
//        4 => "Local Authority Deputy",
//        5 => "Assisted Digital Support",
    ];

    /**
     * @JMS\Type("integer")
     *
     * @var int
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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
