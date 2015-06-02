<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * AuditLogEntry
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
        self::ACTION_USER_ADD, self::ACTION_USER_DELETE, self::ACTION_USER_EDIT
    ];

    /**
     * @var integer
     * @JMS\Groups({"audit_log"});
     */
    private $id;

    /**
     * @var User
     * @JMS\Groups({"audit_log","audit_log_save"});
     */
    private $performedByUser;

    /**
     * @JMS\Type("string")
     * @JMS\Groups({"audit_log","audit_log_save"});
     */
    private $ipAddress;

    /**
     * @var \DateTime
     * 
     * @JMS\Groups({"audit_log","audit_log_save"});
     */
    private $createdAt;

    /**
     * @var string
     * @JMS\Groups({"audit_log","audit_log_save"});
     */
    private $action;

    /**
     * @var User
     * @JMS\Groups({"audit_log","audit_log_save"});
     */
    private $userEdited;


    public function __construct(User $performedBy, $ipAddress, \DateTime $createdAt, $action)
    {
        $this->performedByUser = $performedBy;
        $this->ipAddress = $ipAddress;
        $this->createdAt = $createdAt;
        $this->setAction($action);
        $this->userEdited = null;
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
            throw new \InvalidArgumentException("Action '$action' not valid. Allowed actions: " . implode(',', self::$allowedActions));
        }
        $this->action = $action;

        return $this;
    }
    
    public function setUserEdited(User $userEdited= null)
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

}