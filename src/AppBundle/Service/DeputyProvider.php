<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use AppBundle\Service\Client\RestClient;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class DeputyProvider implements UserProviderInterface
{
    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(RestClient $restClient, Logger $logger)
    {
        $this->restClient = $restClient;
        $this->logger = $logger;
    }

    /**
     * Login passing params to RestClient::login().
     *
     * @param array $credentials
     *
     * @return User
     */
    public function login(array $credentials)
    {
        try {
            $user = $this->restClient->login($credentials);
            // set logged user ID to the restClient (for future requests in this lifespan. e.g. set password on user activation)
          $this->restClient->setLoggedUserId($user->getId());

            return $user;
        } catch (\Exception $e) {
            $this->logger->info(__METHOD__.': '.$e);

            // rethrow 423 (brute-force/locked to grab timestamp)
            if ($e->getCode() == 423) {
                throw $e;
            }

            throw new UsernameNotFoundException("We can't sign you in at this time.", $e->getCode());
        }
    }

    /**
     * Finds user by id.
     *
     * @param int $id
     */
    public function loadUserByUsername($id)
    {
        return $this->restClient
            // the userId needs to be told to the RestClient, as the user is not logged in yet
            ->setLoggedUserId($id)
            ->get('user/'.$id, 'User', ['user', 'role', 'user-login']);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param UserInterface $user
     *
     * @throws UnsupportedUserException
     *
     * @return \AppBundle\Entity\User
     *
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        return $this->loadUserByUsername($user->getId());
    }

    /**
     * @param type $class
     *
     * @return type
     */
    public function supportsClass($class)
    {
        return $class === 'AppBundle\Entity\User';
    }
}
