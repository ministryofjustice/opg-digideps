<?php
namespace AppBundle\Service\Auth\UserProviders;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\User;

/**
 * Get the user from a token (=username) looking at the AuthToken store info
 * throw exception if not found, or the token expired 
 */
interface UserByTokenProviderInterface extends UserProviderInterface
{
    
    /**
     * @param string $randomToken
     * @param User $user
     * 
     * @return string
     */
    public function generateAndStoreToken(User $user);
    
    public function removeToken($token);
}
