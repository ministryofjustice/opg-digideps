<?php
namespace AppBundle\Service\Auth\UserProviders;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\User;

/**
 * Get the user from a token (=username) looking at the AuthToken store info
 * throw exception if not found, or the token expired 
 */
class UserByTokenDoctrine implements UserByTokenProviderInterface
{
     /**
     * @var EntityManager 
     */
    private $em;
    
    /**
     * @var integer 
     */
    private $timeoutSeconds;
    
    /**
     * @var EntityRepository
     */
    private $authTokenRepo;
    
    public function __construct(EntityManager $em, array $options)
    {
        $this->em = $em;
        $this->timeoutSeconds = $options['timeout_seconds'];
        $this->authTokenRepo = $this->em->getRepository('AppBundle\Entity\AuthToken');
    }
    
    /**
     * @param string $username token (String)
     * @return User
     * 
     * @throws BadCredentialsException
     * @throws CredentialsExpiredException
     */
    public function loadUserByUsername($username)
    {
        $authTokenValue = $username;
        $authTokenEntity = $this->authTokenRepo->find($authTokenValue);

        /** @var $token  AuthToken */
        if (!$authTokenEntity) {
            throw new BadCredentialsException('Token invalid');
        }
        if ($authTokenEntity->isExpired($this->timeoutSeconds)) {
            throw new CredentialsExpiredException('Token expired');
        }
        
        return $authTokenEntity->getUser();
    }

    /**
     * not implemented
     */
    public function refreshUser(\Symfony\Component\Security\Core\User\UserInterface $user)
    {
    }


    public function supportsClass($class)
    {
       return 'AppBundle\Entity\User' === $class
            || is_subclass_of($class, 'AppBundle\Entity\User');
    }
    
    /**
     * @param string $randomToken
     * @param User $user
     * 
     * @return string
     */
    public function generateAndStoreToken(User $user)
    {
        // remove existing tokens
        $this->em->createQuery('DELETE FROM AppBundle\Entity\AuthToken at WHERE at.user = :user')
            ->setParameter(':user', $user)
            ->execute();
        
        // recreate and persist token
        $randomToken = $user->getId() . '_' . sha1(microtime() . spl_object_hash($user) . rand(1,999));
        $authTokenEntity = new \AppBundle\Entity\AuthToken($randomToken, $user);
        $this->em->persist($authTokenEntity);
        $this->em->flush($authTokenEntity);
        
        return $authTokenEntity->getToken();
    }
    
    public function removeToken($token)
    {
        // remove existing tokens
        return $this->em
            ->createQuery('DELETE FROM AppBundle\Entity\AuthToken at WHERE at.token = :token')
            ->setParameter(':token', $token)
            ->execute();
    }

}
