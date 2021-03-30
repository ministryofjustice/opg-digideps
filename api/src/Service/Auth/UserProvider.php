<?php

namespace App\Service\Auth;

use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Get the user from a token (=username) looking at the AuthToken store info
 * throw exception if not found, or the token expired.
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var Client
     */
    private $redis;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $timeoutSeconds;

    /**
     * @var array
     */
    private $options;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(EntityManagerInterface $em, Client $redis, LoggerInterface $logger, array $options, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->redis = $redis;
        $this->logger = $logger;
        $this->timeoutSeconds = $options['timeout_seconds'];
        $this->options = $options;
        $this->userRepository = $userRepository;
    }

    /**
     * Called by HeaderTokenAuthenticator::authenticateToken() for each request.
     *
     * @param string $username token (String)
     *
     * @throws RuntimeException with specific codes, in order to avoid being wrapped and losing their` type
     *
     * @return User
     *
     */
    public function loadUserByUsername($username)
    {
        $token = $username;

        $userId = $this->redis->get($token);
        if (!$userId) {
            throw new \RuntimeException("Token $username expired", 419);
        }

        /** @var User|null $user */
        $user = $this->userRepository->find($userId);

        if (!$user) {
            $this->logger->warning("user $userId not found");
            throw new \RuntimeException('User associated to token not found', 419);
        }

        // refresh token creation time
        $this->redis->expire($token, $this->timeoutSeconds);

        return $user;
    }

    /**
     * not implemented.
     */
    public function refreshUser(\Symfony\Component\Security\Core\User\UserInterface $user)
    {
    }

    public function supportsClass($class)
    {
        return 'App\Entity\User' === $class || is_subclass_of($class, 'App\Entity\User');
    }

    /**
     * @param User   $user
     *
     * @return string
     */
    public function generateRandomTokenAndStore(User $user)
    {
        $token = $user->getId() . '_' . sha1(microtime() . spl_object_hash($user) . rand(1, 999));

        $this->redis->set($token, $user->getId());
        $this->redis->expire($token, $this->timeoutSeconds);

        return $token;
    }

    public function removeToken($token)
    {
        return $this->redis->set($token, null);
    }
}
