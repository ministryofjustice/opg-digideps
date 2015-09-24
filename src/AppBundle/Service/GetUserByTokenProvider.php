<?php
namespace AppBundle\Service;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;


class UserProvider implements UserProviderInterface
{
    protected $userRepository;

    public function __construct($entityManager)
    {
        $this->userRepository = $entityManager->getRepository('AppBundle:User');
    }

    public function loadUserByUsername($email) 
    {
        $user = $this->userRepository->createQueryBuilder('u')
                                    ->where('u.email = :email')
                                    ->setParameter('email', $email)
                                    ->getQuery()
                                    ->getOneOrNullResult();
        
        if (null === $user) {
            $message = sprintf(
                'Unable to find an active admin AppBundle:User object identified by "%s".',
                $email
            );
            throw new UsernameNotFoundException($message);
        }
        return $user;
    }
    
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
        
        return $this->userRepository->find($user->getId());
    }
    
    public function supportsClass($class)
    {
        return $this->userRepository->getClassName() === $class
            || is_subclass_of($class, $this->userRepository->getClassName());
    }
}
