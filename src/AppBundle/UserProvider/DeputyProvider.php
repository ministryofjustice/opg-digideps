<?php
namespace AppBundle\UserProvider;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use AppBundle\Service\ApiClient;

class DeputyProvider implements UserProviderInterface
{
    /**
     * @var ApiClient
     */
    private $apiclient;
    
    
    public function __construct(ApiClient $apiclient)
    {
        $this->apiclient = $apiclient;
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
            return $this->apiclient->getEntity('User', 'find_user_by_email', [ 'parameters' => [ 'email' => $email ] ]);
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