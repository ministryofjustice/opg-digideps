<?php
namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Bridge\Monolog\Logger;
use AppBundle\Entity\User;
use AppBundle\Service\Client\RestClient;

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
     * Login passing params to RestClient::login()
     * 
     * @param array $credentials
     * 
     * @return User
     */
    public function login(array $credentials) 
    {
        try {
           return $this->restClient->login($credentials);
        } catch(\Exception $e) {
            $this->logger->info(__METHOD__ . ': ' . $e);
            
            throw new UsernameNotFoundException("We can't log you in at this time.");
        }
    }
    
    /**
     * Finds user by id
     * 
     * @param integer $id
     */
    public function loadUserByUsername($id) 
    {
        return $this->restClient->get('user/'.$id, 'User');
    }
    
    
    /**
     * @codeCoverageIgnore
     * 
     * @param UserInterface $user
     * @return  \AppBundle\Entity\User
     * @throws UnsupportedUserException
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
     * @return type
     */
    public function supportsClass($class)
    {
        return $class === "AppBundle\Entity\User";
    }
}