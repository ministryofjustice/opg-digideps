<?php

namespace App\Service;

use App\Entity\User;
use App\Service\Client\RestClient;
use App\Service\Client\RestClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class DeputyProvider implements UserProviderInterface
{
    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(RestClientInterface $restClient, LoggerInterface $logger)
    {
        $this->restClient = $restClient;
        $this->logger = $logger;
    }

    /**
     * Login passing params to RestClient::login().
     *
     * @param array $credentials see RestClient::login()
     *
     * @return User
     */
    public function login(array $credentials)
    {
        try {
            [$user, $authToken] = $this->restClient->login($credentials);

            // set logged user ID to the restClient (for future requests in this lifespan. e.g. set password on user activation)
            $this->restClient->setLoggedUserId($user->getId());

            return $user;
        } catch (\Throwable $e) {
            $this->logger->info(__METHOD__.': '.$e);

            // rethrow 423 (brute-force/locked to grab timestamp)
            if (423 == $e->getCode()) {
                throw $e;
            }

            throw new UserNotFoundException('We do not recognise your email address or password - please try again.', $e->getCode());
        }
    }

    public function loadUserByIdentifier(string $identifier): User
    {
        return $this->restClient
            // the userId needs to be told to the RestClient, as the user is not logged in yet
            ->setLoggedUserId(intval($identifier))
            ->get(
                'user/'.$identifier, 'User',
                ['user', 'role', 'user-login', 'team-names', 'user-teams', 'team', 'user-organisations']
            );
    }

    public function loadUserByUsername(string $username)
    {
        throw new \RuntimeException('Method should not be called, and removed after symfony 6 upgrade');
    }

    /**
     * @codeCoverageIgnore
     *
     * @return User
     *
     *@throws UnsupportedUserException
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }

        return $this->loadUserByIdentifier($user->getId());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
