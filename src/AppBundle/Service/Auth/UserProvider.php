<?php

namespace AppBundle\Service\Auth;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Predis\Client as PredisClient;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Get the user from a token (=username) looking at the AuthToken store info
 * throw exception if not found, or the token expired 
 */
class UserProvider implements UserProviderInterface
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
     * @var Logger 
     */
    private $logger;

    /**
     * @var integer 
     */
    private $timeoutSeconds;

    /**
     * @var integer 
     */
    private static $timeoutOverride = null;


    public function __construct(EntityManager $em, PredisClient $redis, Logger $logger, array $options)
    {
        $this->em = $em;
        $this->redis = $redis;
        $this->logger = $logger;
        $this->setTimeoutSeconds($options['timeout_seconds']);
    }


    /**
     * Called by HeaderTokenAuthenticator::authenticateToken() for each request
     * 
     * @param string $username token (String)
     * @return User
     * 
     * @throws RuntimeException with specific codes, in order to avoid being wrapped and losing their` type
     */
    public function loadUserByUsername($username)
    {
        $token = $username;

        $this->logger->info("Trying login with token $token ");

        list ($userId, $createdAt) = $this->redisGet($token);
        if (!$userId) {
            $this->logger->warning("token $username  not found");
            throw new \RuntimeException('Token non existing or not valid', 401);
        }
        if (($createdAt + $this->timeoutSeconds) < time()) {
            $this->logger->warning("token $username expired");
            throw new \RuntimeException('Token expired', 419);
        }

        $user = $this->em->getRepository('AppBundle\Entity\User')->find($userId);
        if (!$user) {
            $this->logger->warning("user $userId not found");
            throw new \RuntimeException('User associated to token not found', 401);
        }

        // refresh token creation time
        $this->redisStore($token, $userId);

        return $user;
    }


    /**
     * not implemented
     */
    public function refreshUser(\Symfony\Component\Security\Core\User\UserInterface $user)
    {
        
    }


    public function supportsClass($class)
    {
        return 'AppBundle\Entity\User' === $class || is_subclass_of($class, 'AppBundle\Entity\User');
    }


    /**
     * @param string $token
     * @param User $user
     * 
     * @return string
     */
    public function generateRandomTokenAndStore(User $user)
    {
        $token = $user->getId() . '_' . sha1(microtime() . spl_object_hash($user) . rand(1, 999));

        $this->redisStore($token, $user->getId());

        return $token;
    }


    public function removeToken($token)
    {
        return $this->redis->set($token, null);
    }


    /**
     * @param string $token
     * @param integer $userId
     */
    private function redisStore($token, $userId)
    {
        return $this->redis->set($token, serialize([$userId, time()]));
    }


    /**
     * @param type $token
     * @return array [userId, createdAt]
     */
    private function redisGet($token)
    {
        $storedData = unserialize($this->redis->get($token));
        if (!$storedData || !is_array($storedData) || count($storedData) !== 2) {
            return [null, null];
        }

        return $storedData;
    }
    
    
    /**
     * @param integer $timeoutSeconds
     */
    private function setTimeoutSeconds($timeoutSeconds)
    {
        $this->timeoutSeconds = $timeoutSeconds;
        // override if the static property is set
        if (null !== self::$timeoutOverride) {
            $this->timeoutSeconds = self::$timeoutOverride;
        }
    }
    
    private function getTimeoutSeconds()
    {
        return $this->timeoutSeconds;
    }


    /**
     * @param integer $timeoutSeconds
     */
    public static function overrideTimeoutSeconds($timeoutSeconds)
    {
        self::$timeoutOverride = $timeoutSeconds;
    }

}