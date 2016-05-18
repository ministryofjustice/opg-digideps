<?php

namespace AppBundle\Form\Traits;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContext;

trait HasSecurityContextTrait
{
    /**
     * @var SecurityContext
     */
    private $securityContext;

    /**
     * @return SecurityContext
     */
    public function getSecurityContext()
    {
        return $this->securityContext;
    }

    /**
     * @param SecurityContext $securityContext
     */
    public function setSecurityContext(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @return User
     */
    public function getLoggedUser()
    {
        return $this->getSecurityContext()->getToken()->getUser();
    }

    /**
     * @return string
     */
    public function getLoggedUserEmail()
    {
        return ($this->getLoggedUser() instanceof User) ? $this->getLoggedUser()->getEmail() : null;
    }
}
