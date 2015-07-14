<?php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

class DUserPasswordValidator extends UserPasswordValidator
{
    private $securityContext;
    private $encoderFactory;
    
    public function __construct(SecurityContextInterface $securityContext,EncoderFactoryInterface $encoderFactory) 
    {
        $this->securityContext = $securityContext;
        $this->encoderFactory = $encoderFactory;
        parent::__construct($securityContext, $encoderFactory);
    }
    
    public function validate($password, Constraint $constraint)
    {
        $user = $this->securityContext->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            throw new ConstraintDefinitionException('The User object must implement the UserInterface interface.');
        }
        
        $encoder = $this->encoderFactory->getEncoder($user);
        
        if(!empty($password)){
            if (!$encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt())) {
                $this->context->addViolation($constraint->message);
            }
        }
    }
}