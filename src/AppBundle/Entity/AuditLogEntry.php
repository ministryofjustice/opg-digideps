<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * AuditLogEntry.
 *
 * @ORM\Table(name="audit_log_entry")
 * @ORM\Entity
 */
class AuditLogEntry
{
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_USER_ADD = 'user_add';
    const ACTION_USER_EDIT = 'user_edit';
    const ACTION_USER_DELETE = 'user_delete';

    /**
     * @JMS\Exclude
     */
    private static $allowedActions = [
        self::ACTION_LOGIN, self::ACTION_LOGOUT,
        self::ACTION_USER_ADD, self::ACTION_USER_DELETE, self::ACTION_USER_EDIT,
    ];

    /**
     * @var int
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="account_id_seq", allocationSize=1, initialValue=1)
     * @JMS\Groups({"audit_log"})
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="performed_by_user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @JMS\Groups({"audit_log"})
     */
    private $performedByUser;

    /**
     * @ORM\Column(name="performed_by_user_name", type="string", length=150, nullable=false)
     *
     * @var string
     * @JMS\Groups({"audit_log"})
     * @JMS\Type("string")
     */
    private $performedByUserName;

    /**
     * @var string
     * @ORM\Column(name="performed_by_user_email", type="string", length=150, nullable=false)
     * @JMS\Groups({"audit_log"})
     * @JMS\Type("string")
     */
    private $performedByUserEmail;

    /**
     * @var int
     * @ORM\Column(name="ip_address", type="string", length=15, nullable=false)
     * @JMS\Groups({"audit_log"})
     */
    private $ipAddress;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     * @JMS\Groups({"audit_log"})
     * @JMS\Type("DateTime")
     */
    private $createdAt;

    /**
     * @var string
     * @ORM\Column(name="action", type="string", length=15, nullable=false)
     * @JMS\Groups({"audit_log"})
     * @JMS\Type("string")
     */
    private $action;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="user_edited_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @JMS\Groups({"audit_log"})
     */
    private $userEdited;

    /**
     * @var string
     * @ORM\Column(name="user_edited_name", type="string", length=150, nullable=true)
     * @JMS\Groups({"audit_log"})
     * @JMS\Type("string")
     */
    private $userEditedName;

    /**
     * @var string
     * @ORM\Column(name="user_edited_email", type="string", length=150, nullable=true)
     * @JMS\Groups({"audit_log"})
     * @JMS\Type("string")
     */
    private $userEditedEmail;

    /**
     * @param User $user
     */
    public function setPerformedByUser(User $user)
    {
        $this->performedByUser = $user;
        $this->performedByUserName = $user->getFullName();
        $this->performedByUserEmail = $user->getEmail();

        return $this;
    }

    /**
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @param User $user
     */
    public function setUserEdited(User $user)
    {
        $this->userEdited = $user;
        $this->userEditedName = $user->getFullName();
        $this->userEditedEmail = $user->getEmail();

        return $this;
    }

    /**
     * @param string $action must be among the self::$allowedActions in this class
     */
    public function setAction($action)
    {
        if (!in_array($action, self::$allowedActions)) {
            throw new \InvalidArgumentException("Action '$action' not valid. Allowed actions: ".implode(',', self::$allowedActions));
        }
        $this->action = $action;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getPerformedByUser()
    {
        return $this->performedByUser;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return User
     */
    public function getUserEdited()
    {
        return $this->userEdited;
    }
}
