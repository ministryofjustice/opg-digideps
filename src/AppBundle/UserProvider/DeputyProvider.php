<?php
namespace AppBundle\UserProvider;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use AppBundle\Service\ApiClient;

class DeputyProvider implements UserProviderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    
    /**
     * @var string $env
     */
    private $env;
    
    
    public function __construct(ContainerInterface $container, $env)
    {
        $this->container = $container;
        $this->env = $env;
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
        try {
            $apiclient = $this->container->get('apiclient');
            
            if($this->env == 'admin'){
                return $apiclient->getEntity('User', 'find_admin_by_email', [ 'parameters' => [ 'email' => $email ] ]);
            } elseif(in_array($this->env,[ 'develop','staging','ci','prod'])){
                return $apiclient->getEntity('User', 'find_user_by_email', [ 'parameters' => [ 'email' => $email ] ]);
            } else{
                return $apiclient->getEntity('User', 'find_by_email', [ 'parameters' => [ 'email' => $email ] ]);
            }
        } catch (\Exception $e) {
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