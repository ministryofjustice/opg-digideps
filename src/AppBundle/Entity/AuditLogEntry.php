<?php

namespace AppBundle\Entity;

use JMS\Serializer\Annotation as JMS;

/**
 * AuditLogEntry.
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
     * @JMS\Groups({"audit_log"})
     * @JMS\Type("integer")
     */
    private $id;

    /**
     * @var User
     * @JMS\Groups({"audit_log","audit_log_save"})
     * @JMS\Type("AppBundle\Entity\User")
     */
    private $performedByUser;

    /**
     * @JMS\Groups({"audit_log"})
     * @JMS\Type("string")
     */
    private $performedByUserName;

    /**
     * @JMS\Groups({"audit_log"})
     * @JMS\Type("string")
     */
    private $performedByUserEmail;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"audit_log","audit_log_save"})
     */
    private $ipAddress;

    /**
     * @var \DateTime
     *
     * @JMS\Groups({"audit_log","audit_log_save"})
     * @JMS\Type("DateTime")
     */
    private $createdAt;

    /**
     * @var string
     * @JMS\Groups({"audit_log","audit_log_save"})
     * @JMS\Type("string")
     */
    private $action;

    /**
     * @var User
     * @JMS\Groups({"audit_log","audit_log_save"})
     * @JMS\Type("AppBundle\Entity\User")
     */
    private $userEdited;

    /**
     * @JMS\Groups({"audit_log"})
     * @JMS\Type("string")
     */
    private $userEditedName;

    /**
     * @JMS\Groups({"audit_log"})
     * @JMS\Type("string")
     */
    private $userEditedEmail;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @param User $performedByUser
     */
    public function setPerformedByUser(User $performedByUser)
    {
        $this->performedByUser = $performedByUser;

        return $this;
    }

    /**
     * @param string $ipAddress e.g. 123.124.125.126
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
     * @return string IPaddress format e.g. 125.45.56.253
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
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

    public function setUserEdited(User $userEdited = null)
    {
        $this->userEdited = $userEdited;

        return $this;
    }

    /**
     * @return User
     */
    public function getUserEdited()
    {
        return $this->userEdited;
    }

    /**
     * @return User
     */
    public function getPerformedByUser()
    {
        return $this->performedByUser;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getPerformedByUserName()
    {
        return $this->performedByUserName;
    }

    /**
     * @return string
     */
    public function getPerformedByUserEmail()
    {
        return $this->performedByUserEmail;
    }

    /**
     * @return string
     */
    public function getUserEditedName()
    {
        return $this->userEditedName;
    }

    /**
     * @return string
     */
    public function getUserEditedEmail()
    {
        return $this->userEditedEmail;
    }
}
