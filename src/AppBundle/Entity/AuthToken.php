<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\RoleInterface;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Table(name="auth_token")
 * @ORM\Entity
 */
class AuthToken
{ 
    /**
     * @var integer
     *
     * @ORM\Column(name="token", type="string", nullable=false)
     * @ORM\Id
     */
    private $token;
    
    /**
     * @var User
     * 
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var \DateTime
     * 
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;
    
    public function __construct($token, $user)
    {
        $this->token = $token;
        $this->user = $user;
        $this->refreshToken();
    }
    
    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
    
    public function refreshToken()
    {
        $this->createdAt = new \DateTime;
    }
    
    /**
     * @param integer $timeoutSeconds
     * 
     * @return boolean
     */
    public function isExpired($timeoutSeconds)
    {
        return ($this->createdAt->getTimestamp() + $timeoutSeconds) < time();
    }
    
    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

}
