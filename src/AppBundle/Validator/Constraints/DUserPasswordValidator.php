<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Service\Client\RestClient;
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

    public function __construct($securityContext, $restClient)
    {
        $this->restClient = $restClient;
        $this->securityContext = $securityContext;
    }

    public function validate($password, Constraint $constraint)
    {
        $user = $this->securityContext->getToken()->getUser();

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
        return $this->restClient->post('user/' . $user->getId() . '/is-password-correct', [
            'password' => $password,
        ]);
    }
}
