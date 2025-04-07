<?php

namespace App\Validator\Constraints;

use App\Service\Client\RestClient;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

class DUserPasswordValidator extends UserPasswordValidator
{
    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage, RestClient $restClient)
    {
        $this->restClient = $restClient;
        $this->tokenStorage = $tokenStorage;
    }

    public function validate(mixed $password, Constraint $constraint): void
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            throw new ConstraintDefinitionException('The User object must implement the UserInterface interface.');
        }

        if (!empty($password)) {
            if (!$this->isOldPasswordValid($user, $password)) {
                $this->context->addViolation($constraint->message);
            }
        }
    }

    private function isOldPasswordValid($user, $password)
    {
        return $this->restClient->post('user/'.$user->getId().'/is-password-correct', [
            'password' => $password,
        ]);
    }
}
