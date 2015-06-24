<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\OAuthServerBundle\Model\ClientInterface;

/**
 * @ORM\Table(name="access_token")
 * @ORM\Entity
 */
class AccessToken extends BaseAccessToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     *
     * @ORM\ManyToOne(targetEntity="OAuth2Client")
     * @ORM\JoinColumn(nullable=false, name="oauth2_client_id")
     */
    protected $oAuth2client;
    
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
     * Set oAuth2client
     *
     * @param \AppBundle\Entity\OAuth2Client $oAuth2client
     * @return AccessToken
     */
    public function setClient(ClientInterface $oAuth2client)
    {
        $this->oAuth2client = $oAuth2client;

        return $this;
    }

    /**
     * Get oAuth2client
     *
     * @return \AppBundle\Entity\OAuth2Client 
     */
    public function getClient()
    {
        return $this->oAuth2client;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return AccessToken
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
