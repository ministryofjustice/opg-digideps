<?php
namespace AppBundle\UserProvider;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Bridge\Monolog\Logger;

class DeputyProvider implements UserProviderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    
    /**
     * @var string
     */
    private $env;
    
     /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var array 
     */
    private static $envToEndpoint = [
        'admin' => 'user/get-admin-by-email',
        'develop' => 'user/get-user-by-email',
        'staging' => 'user/get-user-by-email',
        'ci' => 'user/get-user-by-email',
        'prod' => 'user/get-user-by-email'
    ];
    
    public function __construct(ContainerInterface $container, Logger $logger, $env)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->env = $env;
    }
    
    /**
     * 
     * @param array $credentials
     * @return type
     */
    public function login(array $credentials) 
    {
        $restclient = $this->container->get('restClient');
        
        try {
           return $restclient->login($credentials['email'], $credentials['password']);
        } catch(\Exception $e) {
            $this->logger->info(__METHOD__ . ': ' . $e);
            
            throw new UsernameNotFoundException("We can't log you in at this time.");
        }
    }
    
    /**
     * Finds user by email
     * 
     * @param string $email
     * @return \AppBundle\Entity\User $user
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername($email) 
    {
        $apiclient = $this->container->get('apiclient');
        
        try {
            $endpoint = isset(self::$envToEndpoint[$this->env]) 
                        ? self::$envToEndpoint[$this->env] : 'user/get-by-email';
            
            return $apiclient->getEntity('User', "{$endpoint}/{$email}");
        } catch (\Exception $e) {
            $this->logger->info(__METHOD__ . ': ' . $apiclient->getLastErrorMessage());

            throw new UsernameNotFoundException("We can't log you in at this time.");
        }
        
    }
    
    /**
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
        return $this->loadUserByUsername($user->getEmail());
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