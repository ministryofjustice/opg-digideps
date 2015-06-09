<?php
namespace AppBundle\Entity;

use FOS\OAuthServerBundle\Entity\AuthCode as BaseAuthCode;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\OAuthServerBundle\Model\ClientInterface;

/**
 * @ORM\Table(name="auth_code")
 * @ORM\Entity
 */
class AuthCode extends BaseAuthCode
{
    /**
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="oAuth2Client")
     * @ORM\JoinColumn(nullable=false, name="oauth2_client_id")
     */
    protected $oAuth2Client;
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     */
    protected $user;

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
     * Set oAuth2Client
     *
     * @param \AppBundle\Entity\oAuth2Client $oAuth2Client
     * @return AuthCode
     */
    public function setClient(ClientInterface $oAuth2Client)
    {
        $this->oAuth2Client = $oAuth2Client;

        return $this;
    }

    /**
     * Get oAuth2Client
     *
     * @return \AppBundle\Entity\oAuth2Client 
     */
    public function getClient()
    {
        return $this->oAuth2Client;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return AuthCode
     */
    public function setUser(UserInterface $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
