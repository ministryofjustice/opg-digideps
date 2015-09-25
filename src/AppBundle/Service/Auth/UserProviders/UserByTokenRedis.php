<?php
namespace AppBundle\Service\Auth\UserProviders;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Predis\Client as PredisClient;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;


/**
 * Get the user from a token (=username) looking at the AuthToken store info
 * throw exception if not found, or the token expired 
 */
class UserByTokenRedis implements UserByTokenProviderInterface
{
     /**
     * @var PredisClient 
     */
    private $redis;
    
     /**
     * @var EntityManager 
     */
    private $em;
    
    /**
     * @var EntityRepository
     */
    private $userRepo;
    
    
    public function __construct(EntityManager $em, PredisClient $redis)
    {
        $this->em = $em;
        $this->redis = $redis;
        $this->userRepo = $this->em->getRepository('AppBundle\Entity\User');
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
        $userId = $this->redis->get($username);
        if (!$userId) {
            throw new BadCredentialsException('Token non existing');
        }
//        if ($Use->isExpired($this->timeoutSeconds)) {
//            throw new CredentialsExpiredException('Token expired');
//        }

        $User = $this->userRepo->find($userId);

        /** @var $token  AuthToken */
        if (!$User) {
            throw new BadCredentialsException('User associated to token not found');
        }
        
        
        return $User;
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
        // recreate and persist token
        $randomToken = $user->getId() . '_' . sha1(microtime() . spl_object_hash($user) . rand(1,999));
        $this->redis->set($randomToken, $user->getId());
        
        return $randomToken;
    }
    
    public function removeToken($token)
    {
        return $this->redis->set($token, null);
    }

   

}
