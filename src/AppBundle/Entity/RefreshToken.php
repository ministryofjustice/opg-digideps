<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\RefreshToken as BaseRefreshToken;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\OAuthServerBundle\Model\ClientInterface;


/**
 * @ORM\Table(name="refresh_token")
 * @ORM\Entity
 */
class RefreshToken extends BaseRefreshToken
{
    /**
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
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE")
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
     * @return RefreshToken
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
     * @return RefreshToken
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
