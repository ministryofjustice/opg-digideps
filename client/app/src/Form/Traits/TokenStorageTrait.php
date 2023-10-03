<?php

namespace App\Form\Traits;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

trait TokenStorageTrait
{
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @return TokenStorage
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    public function setTokenStorage(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return User
     */
    public function getLoggedUser()
    {
        return $this->getTokenStorage()->getToken()->getUser();
    }

    /**
     * @return string
     */
    public function getLoggedUserEmail()
    {
        return ($this->getLoggedUser() instanceof User) ? $this->getLoggedUser()->getEmail() : null;
    }
}
